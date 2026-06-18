# Performance Review — Bottlenecks & Fixes

Reviewed 2026-06-18. Scope: ProjectManagment (board/leads hot path) + cross-cutting workspace scoping. Ordered by impact.

---

## 1. WorkspaceScope runs an uncached DB query on *every* scoped query — CRITICAL

[`app/Models/Scopes/WorkspaceScope.php:18`](../app/Models/Scopes/WorkspaceScope.php#L18)

```php
$builder->whereIn(
    $model->getTable().'.workspace_id',
    Auth::user()->workspaces()->pluck('id')->all(),  // <-- DB query, every query
);
```

Every workspace-scoped model query (Lead, LeadAnswer, Field, Form, Stage, Project…) triggers an extra `select id from workspaces where user_id = ?`. The board `show()` fires ~10–15 model queries per request → ~10–15 *identical* redundant workspace lookups stacked on top.

Same problem on writes: [`BelongsToWorkspace.php:47`](../app/Models/Concerns/BelongsToWorkspace.php#L47) `currentWorkspaceId()` re-queries `workspaces()->value('id')` on every `creating` **and** `updating`. Bulk answer sync (N fields) = N extra workspace queries.

**Fix:** memoize the workspace id list for the request. Cheapest — a per-request static on the user resolve, or a request-scoped cache:

```php
// WorkspaceScope
protected static array $cache = [];

public function apply(Builder $builder, Model $model): void
{
    if (! Auth::check()) {
        return;
    }

    $userId = Auth::id();
    $ids = self::$cache[$userId] ??= Auth::user()->workspaces()->pluck('id')->all();

    $builder->whereIn($model->getTable().'.workspace_id', $ids);
}
```

Reset the static between requests (it already dies with the PHP-FPM request, so fine for web; reset in tests via a `setUp`). Apply the same `??=` memoization to `currentWorkspaceId()`. Drops ~10–15 queries/board-request to 1.

---

## 2. `Lead::title()` fallback fires a fresh query per lead — N+1

[`Modules/ProjectManagment/app/Models/Lead.php:72`](../Modules/ProjectManagment/app/Models/Lead.php#L72)

```php
public function title(): string
{
    $titleAnswer = $this->answers->first(fn (LeadAnswer $answer) => $answer->field->is_title);

    return $titleAnswer?->value
        ?: $titleAnswer?->field->default_value
        ?: $this->answers()->first()->value;   // <-- new query + null-deref risk
}
```

`title()` is called per lead in [`LeadResource`](../Modules/ProjectManagment/app/Http/Resources/LeadResource.php#L20). The board renders up to 7 stages × 30 = 210 leads. When no title answer is found, `$this->answers()->first()` runs a **fresh DB query per lead** (210 queries) and `->value` throws if the lead has zero answers.

**Fix:** use the already-eager-loaded collection, never re-query:

```php
return $titleAnswer?->value
    ?: $titleAnswer?->field->default_value
    ?: $this->answers->first()?->value     // collection, not relation
    ?: '';
```

`answers.field` is already eager-loaded in [`LeadRepository::boardLeads`](../Modules/ProjectManagment/app/Repositories/LeadRepository.php#L61), so `$this->answers` and `->field` are free. The `?: ''` removes the null-deref crash.

---

## 3. Board leads have no composite index — filesort per stage

[`Modules/ProjectManagment/database/migrations/2026_06_17_120100_create_leads_table.php:19`](../Modules/ProjectManagment/database/migrations/2026_06_17_120100_create_leads_table.php#L19) indexes only `stage_id`.

[`LeadRepository::boardLeads`](../Modules/ProjectManagment/app/Repositories/LeadRepository.php#L55) does:

```sql
ROW_NUMBER() OVER (PARTITION BY stage_id ORDER BY created_at DESC, id DESC)
WHERE stage_id IN (...)
```

The single-column `stage_id` index covers the filter but not the ordering → MySQL filesorts within each partition. `filter()` ([`LeadRepository.php:16`](../Modules/ProjectManagment/app/Repositories/LeadRepository.php#L16)) has the same `latest('created_at')` problem.

**Fix:** composite index matching the access pattern.

```php
$table->index(['stage_id', 'created_at', 'id'], 'leads_stage_created_index');
```

---

## 4. `filter()` returns unbounded `->get()` — memory/latency blowup

[`LeadRepository::filter`](../Modules/ProjectManagment/app/Repositories/LeadRepository.php#L12) loads **every** matching lead with `answers.field` eager-loaded, no limit. A stage with thousands of leads pulls all rows + all answers into memory on one request.

**Fix:** paginate. Cursor pagination is already the project pattern (see [`ActivityLogger::filter`](../Modules/ActivityLog/app/Services/ActivityLogger.php#L37) → `CursorPaginator`).

```php
return $this->lead->filter($filters)
    ->with('answers.field')
    ->latest('leads.created_at')
    ->cursorPaginate(30);
```

Propagate the paginator through `LeadService::filter` and the controller response.


Longer term, if price filtering is common, store numeric answers in a dedicated typed column (`value_numeric DECIMAL`) and index that. <!-- ponytail: cast-in-query now; typed column only if price filtering becomes a hot path -->

---

## 6. `syncAnswers` does updateOrCreate in a loop — 2N queries per save

[`LeadRepository::syncAnswers`](../Modules/ProjectManagment/app/Repositories/LeadRepository.php#L89) runs one `updateOrCreate` (a SELECT + INSERT/UPDATE) per field. Low volume per lead, but it's pure overhead on a path inside a transaction.

**Fix (only if save latency shows up):** single `upsert` keyed on the existing `(lead_id, field_id)` unique index:

```php
$answersArray = collect($answers)->map(fn ($value, $fieldId) => [
    'workspace_id' => $lead->workspace_id,
    'lead_id' => $lead->id,
    'field_id' => $fieldId,
    'value' => $value,
])->values()->all();

$lead->answers()->upsert(
    $answersArray,
    ['lead_id', 'field_id'],
    ['value'],
);
```

`upsert` bypasses the `BelongsToWorkspace` `creating` hook, so `workspace_id` (NOT NULL) **must** be in each row — copy it from the parent `$lead`. Lower priority — measure first.

---

## Non-issues (verified good)

- **Activity logging** is queued via `RecordActivityLog::dispatch` ([ActivityLogger.php:25](../Modules/ActivityLog/app/Services/ActivityLogger.php#L25)) — off the request path. Good.
- **Board skeleton** is cached 10 min and invalidated on mutation (`BoardService::forgetSkeleton`). Good — keep it; fixing #1 makes the per-request lead queries cheap too.
- **`countsByStage`** is a single grouped aggregate, not per-stage. Good.

---

## Priority order

1. **#1 WorkspaceScope memoization** — biggest win, touches every endpoint, low risk.
2. **#2 title() N+1** — removes up to 210 queries on the main board view.
3. **#3 composite index** — one migration.
4. **#4 lead filter pagination** — prevents worst-case OOM.
6. #6 upsert — defer until measured.

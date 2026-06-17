# Board Feature — Spec

Status: implemented.

## Goal

One endpoint to load a project board: project info + form + first stages, each with its first leads. Built for fast initial paint + infinite scroll on two axes.

## Endpoint

`GET projects/{project}/board` → `BoardController@show` → `BoardService::show`.

## Load rules

- **Skeleton** (project + form with fields/options + stage list) is **cached** per project. It rarely changes.
- **Leads** are **never cached** — fetched live every request.
- Stages: first **7** returned (`STAGES_LIMIT`). `has_more_stages` flag tells the front to scroll for more.
- Leads: max **30 per stage** (`LEADS_PER_STAGE`). Fewer → all returned. Per-stage `has_more` flag tells the front to scroll for more leads in that stage.

## Lead query — single flat window query

All 7 stages' leads come from ONE query, capped 30 per stage via a window function:

```sql
SELECT * FROM (
  SELECT leads.*, ROW_NUMBER() OVER (PARTITION BY stage_id ORDER BY created_at DESC, id DESC) AS rn
  FROM leads
  WHERE stage_id IN (:ids) AND workspace_id IN (:ws)
) ranked
WHERE rn <= 30
```

- `PARTITION BY stage_id` → count restarts each stage.
- `rn <= 30` → caps per stage. No special case for stages with < 30.
- Service groups the flat rows by `stage_id` and attaches them onto the cached stage skeleton.
- `has_more` per stage = `COUNT(*) by stage_id > 30` (one extra grouped count query).
- Ordering key is `created_at DESC` for now. Switch to a board `position` column if leads become drag-orderable.

## Response shape

```json
{
  "data": {
    "project": { "id": 1, "title": "...", "avatar_name": "...", "created_at": "..." },
    "form":    { "id": 1, "name": "...", "fields": [ ... with options ... ] },
    "has_more_stages": true,
    "stages": [
      { "id": 10, "name": "drafted", "sort_order": 1, "has_more": true, "leads": [ ...≤30... ] }
    ]
  }
}
```

Front buckets are already grouped server-side; it uses `has_more_stages` for horizontal scroll and per-stage `has_more` for vertical scroll.

## Caching

- Key: `board_skeleton:{project_id}` (project id is globally unique + workspace-scoped on read, so no workspace in key).
- TTL: 10 min safety net.
- Invalidated on any structural change via `BoardCacheObserver` (saved/deleted), registered on `Project`, `Form`, `Stage`, `Field`, `FieldOption`. The observer resolves each model's `project_id` and calls `BoardService::forgetSkeleton`. Lead writes do NOT bust it (leads aren't cached).

## Load-more endpoints (reuse existing)

- More stages: `GET projects/{project}/stages` (add cursor pagination when wired).
- More leads in a stage: `GET projects/{project}/stages/{stage}/leads` (add cursor pagination when wired).

> Cursor pagination on those two is a follow-up — the board initial-load endpoint is what this feature delivers.

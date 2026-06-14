# Feature: Project Forms + Lead Responses

Module: **ProjectManagement** (existing — related "task/list feature" per [CLAUDE.md](../CLAUDE.md) grouping rule, so no new module).
Follow [architecture.md](../instructions/architecture.md) and [how-to-code.md](../instructions/how-to-code.md).

---

## 1. Goal

A project owner defines a **form** (a set of typed fields) once per project. Every **lead** on that project's board answers the same form and stores its own **values**. ClickUp custom-fields model.

Board hierarchy (ClickUp-style):

```
Project
  ├─ Form              (one per project — the field schema / template)
  │    └─ Field[]  (Text | Select(+Options) | Map | Date | DateTime)
  └─ Stage[]
       └─ Lead[]                 (sort_order, fractional gap ordering)
            └─ LeadFieldValue[]  (one answer per Field)
```

A lead's answers reference `field_id`, so the form is derivable; the lead does not need its own copy of the fields.

### Locked decisions
- **Form scope:** per project (shared template). Change a field once → applies to all leads. Answers are comparable across leads (reporting/filter later).
- **Value storage:** single JSON `value` column per answer, cast by field type. Simplest schema; SQL filtering on values is intentionally out of scope for MVP.

---

## 2. Dependencies / pre-requisites

> **Stage and Lead are specced but not implemented.** [tests/Feature/LeadOrderingTest.php](../tests/Feature/LeadOrderingTest.php) and [route.txt](../route.txt) describe `Stage`, `Lead`, `LeadService::move()` (fractional `sort_order`, gap = 100000, rebalance when gap too small) and nested routes, but the module folder currently holds **Project only**. The form feature sits on top of `Lead`, so:
> - Build / confirm **Stage + Lead** first (or in the same effort).
> - Module folder is spelled `Modules/ProjectManagment` while the test namespaces use `Modules\ProjectManagement` (correct). Resolve this rename before wiring new classes, or the new files won't autoload consistently.

---

## 3. Field types

`Modules\ProjectManagement\Enums\FieldType` (string-backed, TitleCase keys):

| Key | value | Stored answer shape | Notes |
|-----|-------|---------------------|-------|
| `Text` | `text` | `"some string"` | `max:` from `config` later |
| `Select` | `select` | `5` (a `field_options.id`) | options live on the field definition; MVP single-select |
| `Map` | `map` | `{ "lat": 30.04, "lng": 31.23 }` | lat `-90..90`, lng `-180..180` |
| `Date` | `date` | `"2026-06-14"` | `Y-m-d` |
| `DateTime` | `datetime` | `"2026-06-14T13:30:00Z"` | ISO 8601 |

The enum is the single source of truth for "what types exist" **and** "how to validate a value of this type" (a `rules(): array` / `validate($value)` helper on the enum, used by the service — see §6).

---

## 4. Data model

No `workspace_id` on any new table — every one is reached through `Project`, which already carries `workspace_id` and the `WorkspaceScope`. Tenant isolation comes from fetching the parent `Project` through its repository (`findOrFail` → 404 for a foreign project). This matches how Stage/Lead are scoped.

### `forms`
| col | type | notes |
|-----|------|-------|
| id | pk | |
| project_id | FK → projects, cascadeOnDelete | **unique** → one form per project (MVP) |
| name | string, nullable | optional label |
| timestamps | | |

### `fields`
| col | type | notes |
|-----|------|-------|
| id | pk | |
| form_id | FK → forms, cascadeOnDelete | |
| label | string | |
| type | string (FieldType) | enum cast |
| is_required | bool, default false | enforced at value submit |
| sort_order | int | field display order |
| config | json, nullable | reserved per-type settings (e.g. text max, map default center) |
| timestamps | | |
| index | (form_id, sort_order) | |

### `field_options`  (Select definitions only)
| col | type | notes |
|-----|------|-------|
| id | pk | |
| field_id | FK → fields, cascadeOnDelete | |
| label | string | shown to user |
| value | string | machine value |
| sort_order | int | |
| timestamps | | |

### `leads`  (build if missing — shape from LeadOrderingTest)
| col | type | notes |
|-----|------|-------|
| id | pk | |
| project_id | FK → projects, cascadeOnDelete | |
| stage_id | FK → stages, cascadeOnDelete | |
| created_by | FK → users | |
| sort_order | int | fractional gap ordering |
| timestamps | | |

### `lead_field_values`  (the answers)
| col | type | notes |
|-----|------|-------|
| id | pk | |
| lead_id | FK → leads, cascadeOnDelete | |
| field_id | FK → fields, cascadeOnDelete | |
| value | json, nullable | shape per field type (§3) |
| timestamps | | |
| unique | (lead_id, field_id) | one answer per field per lead → enables upsert |

**Data-loss callout:** deleting a `field` cascades its options **and** every lead's stored answer for that field. Surface this in the delete endpoint response / confirm at the UI layer.

---

## 5. API surface (nested under project, mirrors existing stage/lead routes)

All under `Route::middleware(['auth:api'])->prefix('v1')`. Controllers type-hint **scalar ids** (`int $project`, `int $field`, `int $lead`) — no route-model binding (architecture rule); the service fetches through the repository so `WorkspaceScope` returns 404 for foreign records.

### Form builder
| Method | URI | Action |
|--------|-----|--------|
| GET | `/projects/{project}/form` | show form + fields + options |
| POST | `/projects/{project}/form/fields` | add field (with options if Select) |
| PUT | `/projects/{project}/form/fields/{field}` | update field / its options |
| DELETE | `/projects/{project}/form/fields/{field}` | remove field (cascades answers) |
| POST | `/projects/{project}/form/fields/reorder` | reorder fields (same pattern as stage reorder) |

The project's `form` row is auto-created on first field add (or when the project is created — decide in build, see §8).

### Lead answers
| Method | URI | Action |
|--------|-----|--------|
| PUT | `/projects/{project}/leads/{lead}/values` | upsert all answers for the lead (bulk) |
| GET | `/projects/{project}/leads/{lead}` | lead + its values (embed `LeadFieldValueResource`) |

### Sample payloads

Add a Select field:
```json
POST /v1/projects/12/form/fields
{
  "label": "Lead Source",
  "type": "select",
  "is_required": true,
  "options": [
    { "label": "Facebook", "value": "facebook" },
    { "label": "Referral", "value": "referral" }
  ]
}
```

Submit a lead's answers:
```json
PUT /v1/projects/12/leads/88/values
{
  "values": [
    { "field_id": 1, "value": "John Doe" },
    { "field_id": 2, "value": 5 },
    { "field_id": 3, "value": { "lat": 30.0444, "lng": 31.2357 } },
    { "field_id": 4, "value": "2026-06-14" },
    { "field_id": 5, "value": "2026-06-14T13:30:00Z" }
  ]
}
```

---

## 6. Validation strategy (the tricky part — dynamic fields)

Two layers, to respect "no DB in FormRequest / business logic in Service":

1. **FormRequest (structural):** `SubmitLeadValuesRequest` validates the envelope only — `values` is a list, each item has an integer `field_id` and a present `value`. No DB lookups.
2. **Service (semantic):** `LeadResponseService` loads the project's fields via repository, then for each submitted value validates against the field's `FieldType` and `is_required`, throwing a domain `FormValidationException` (extends the existing exception pattern, e.g. `AuthException`) → **422** with per-field messages.

Per-type semantic rules (driven off `FieldType`):

| Type | Rule |
|------|------|
| Text | string, respect `config.max` if set |
| Select | integer that is an `id` in **this field's** `field_options` |
| Map | object `{lat,lng}`, both numeric, lat `-90..90`, lng `-180..180` |
| Date | parseable `Y-m-d` |
| DateTime | parseable ISO 8601 |

Required enforcement: on submit, every `is_required` field must have a non-empty value. A lead may exist before answers are submitted; required-ness is checked at the values endpoint, not at lead creation.

---

## 7. Layered files to create (ProjectManagement module)

Per [how-to-code.md](../instructions/how-to-code.md) — Controller → Service → Repository → Model, constructor DI only.

- **Enums:** `FieldType`
- **Models:** `Form`, `Field`, `FieldOption`, `LeadFieldValue` (+ `Lead`, `Stage` if not present). Each model: relationships, casts (`value` → array/json, `type` → `FieldType`), and `scopeFilter` where a filter endpoint exists.
- **Migrations:** `forms`, `fields`, `field_options`, `lead_field_values` (+ `leads`, `stages` if missing).
- **Repositories:** `FormRepository`, `FieldRepository`, `LeadFieldValueRepository` (queries only, `$this->model->newQuery()`).
- **Services:** `FormService` (build/maintain form + fields + options, transactional when options included), `LeadResponseService` (validate + upsert answers). May fold the response logic into the existing `LeadService` if it stays small.
- **Requests:** `StoreFieldRequest`, `UpdateFieldRequest`, `ReorderFieldsRequest`, `SubmitLeadValuesRequest`.
- **Resources:** `FormResource`, `FieldResource`, `FieldOptionResource`, `LeadFieldValueResource` (embed values into `LeadResource`).
- **Controllers:** `ProjectFormController` (field CRUD + reorder), `LeadValueController` (submit + show).
- **Events (optional, queued):** `LeadFormSubmitted` → activity-log listener (the project already logs lead creation via `RecordActivityLog`; reuse that pattern, `ShouldQueue`, `$afterCommit = true`).
- **Routes:** add the nested group above to the module's `routes/api.php`.

---

## 8. Build order

1. Resolve the `ProjectManagment` → `ProjectManagement` module spelling, and implement/confirm **Stage + Lead** (migrations, models, repos, services, ordering from `LeadOrderingTest`).
2. `FieldType` enum + the four form/value migrations.
3. Models + relationships + casts.
4. Repositories.
5. `FormService` + `ProjectFormController` + form requests/resources → **form builder works**.
6. `LeadResponseService` + `LeadValueController` + `SubmitLeadValuesRequest` + value validation → **answers work**.
7. Embed values in `LeadResource`; optional `LeadFormSubmitted` event → activity log.
8. Feature tests (Pest): form-build CRUD, each field-type validation (happy + reject), required enforcement, cross-project 404 isolation, value upsert (replace) idempotency.

---

## 9. Open questions (defaults assumed for MVP, revisit before build)

| # | Question | MVP default |
|---|----------|-------------|
| 1 | Multi-select fields? | No — Select stores a single option id. Extend to array later. |
| 2 | Multiple forms per project? | No — one form per project (`forms.project_id` unique). Add `lead.form_id` if this changes. |
| 3 | Delete a field that has answers? | Hard cascade (answers lost). Could soft-delete later. |
| 4 | When is the `forms` row created? | On project create, or lazily on first field add — pick one in build. |
| 5 | File / image field type? | Out of scope for MVP. |

# Leads Feature — Spec

Status: decisions locked, ready to build.

## Decisions (locked)

1. **Title is derived, not stored.** No `title` column on leads. Title = the lead's answer to the title field; falls back to that field's `default_value`; falls back to "Untitled" if neither. Changing the field value or its default_value changes the displayed title — title is always live.
2. **Answered form = normalized `lead_answers` table** (one row per field).
3. **due_date is nullable** (optional on create).
4. **Routes nested under stage**: `projects/{project}/stages/{stage}/leads/...`.
5. Title field designated by `fields.is_title` (exactly one per form).

## Domain recap

```
Project ──has one── Form ──has many── Field ──has many── FieldOption
   │
   └──has many── Stage ──has many── Lead ──has many── LeadAnswer (answered form)
```

- A **Project** has one **Form** (the project's intake form) and many **Stages**.
- A **Stage** has many **Leads**.
- A **Lead** belongs to one Stage and stores **its answered form** — one answer per Field.

## New requirements

### 1. Form must have a title field with a default value

- A created **Form must have at least one Field**. Empty forms are invalid.
- One Field is the **title source**. Its **default value** becomes the **Lead's title**.
- When a Lead is created:
  - title = the lead's answer to the title field, **or** the field's `default_value` if the answer is empty.
- Earlier rule still holds: if a project has **no usable form**, the Lead gets a default title ("Untitled" / project name — see open Q3).

Schema change:
- `fields.default_value` — nullable string, the fallback value.
- Designate the title field. Proposed: `fields.is_title` boolean, exactly one per form. (alt: form.title_field_id — see open Q1.)

### 2. Lead has a fixed due_date

- `leads.due_date` — `datetime`, **not** part of the form answers. Set directly on the Lead.

## Proposed schema

**leads**
| col | type | notes |
|-----|------|-------|
| id | id | |
| stage_id | FK → stages, cascade | |
| due_date | datetime nullable | fixed, not a form answer |
| timestamps | | |

> No `title` column — title is derived (decision 1).

> Carries its own `workspace_id` + `BelongsToWorkspace` (every table is workspace-scoped directly — no child-table exception).

**lead_answers** (the answered form)
| col | type | notes |
|-----|------|-------|
| id | id | |
| lead_id | FK → leads, cascade | |
| field_id | FK → fields, cascade | |
| value | text nullable | raw answer; shape depends on field type |
| timestamps | | |

> unique(lead_id, field_id) — one answer per field per lead.

**fields** (add)
- `default_value` string nullable
- `is_title` boolean default false

## Layers to add (per CLAUDE.md flow)

- Models: `Lead`, `LeadAnswer` (+ `scopeFilter`, relations).
- Migrations: `create_leads_table`, `create_lead_answers_table`, `add_title_fields_to_fields_table`.
- Repository / Service / Controller for Lead (CRUD + filter).
- FormRequests: `StoreLeadRequest`, `UpdateLeadRequest`, `FilterLeadRequest`.
- Resource: `LeadResource` (+ answers).
- Routes: explicit verbs under `projects/{project}/stages/{stage}/leads` (no apiResource).
- Form creation/validation: enforce ≥1 field + exactly one title field with default_value.
- Lead create: title-resolution logic (answer → default_value → fallback).

## Remaining minor question

- No-form fallback title string is "Untitled". Change if you want project name or sequential ("Lead #1").

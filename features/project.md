# Feature: Project

## Purpose
A **Project** is the top-level container a workspace user creates to collect leads through a custom form. Each project owns exactly one form, and that form is built from configurable fields (with options for choice-type fields).

## Module
`Modules/ProjectManagment` — follows Controller → Service → Repository → Model layering.

## Domain model

```
Workspace
  └── Project (workspace_id, title)
        └── Form (one per project)
              └── Field (label, type, is_required, sort_order, config)
                    └── FieldOption (for choice-type fields: select, radio, checkbox)
```

- **Project** — owns `workspace_id` + `title`. Uses `BelongsToWorkspace` trait, so tenant isolation is automatic (no manual `workspace_id` handling).
- **Form** — auto-resolved per project; one form per project. Accessed via `GET projects/{project}/form`.
- **Field** — belongs to a form. `type` is a `FieldType` enum, `config` is a JSON array, ordered by `sort_order`.
- **FieldOption** — belongs to a field; only relevant for choice-type fields. Ordered by `sort_order`.

## Workspace scoping
Every read is constrained to the authenticated user's workspace by the global `WorkspaceScope`. Clients never send `workspace_id`. A foreign id throws `ModelNotFoundException` → 404.

## Endpoints (`auth:api`, prefix `v1`)

| Method | URI | Action |
|--------|-----|--------|
| GET | `projects` | List projects (filterable by `title`) |
| POST | `projects` | Create project |
| GET | `projects/{project}` | Show project |
| PUT/PATCH | `projects/{project}` | Update project |
| DELETE | `projects/{project}` | Delete project |
| GET | `projects/{project}/form` | Show the project's form |
| — | `projects/{project}/fields` | Field CRUD (apiResource) |
| — | `projects/{project}/fields/{field}/options` | Field option CRUD (apiResource) |

## Project creation flow
1. User creates a project: submits the project **name** (`title`) and an **avatar_name**.
2. On submit, the project is persisted under the authenticated user's workspace.

## Form building flow
After the project is created, the user gets a **Form** tab to build the project's form.
- Each project has one form; the form holds an ordered list of fields.
- Supported field types: **Text**, **Phone**, **Email**, **Map** (lat/lng), **Checkbox**.

## Stage flow
After the project and form are set up, the user gets a modal to add **stages** — the pipeline columns a lead moves through (e.g. `todo`, `in progress`, etc.). The user chooses each stage's name freely.
- On project creation a default stage named **`drafted`** is created automatically.
- Stages belong to the project and are ordered (`sort_order`).

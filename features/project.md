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


## Update 1 -> How the system of the project really work 
The project is the main core of the project where every thing connected to it so basically we have as we said before project contain form contain leads each leads has its own form answers at this point every thing is cool what we need to add 

1) Project mode 

the project have 3 modes
- Sheet Mode
- Kanban Mode
- Calender Mode 

# Sheet Mode 
as we said the lead contain its answer of the form but i want the user to show the leads in form google sheets where you will add the label of the form in the header and the table under it will be the answer of each lead like google sheet

# Kanban Mode 
as we said before each lead contain its answer of the form and each form contain default Field which we will use it as title so i want in kanban to only show the title and due date of the lead and when the user click on the lead a sidenav bar will appear to show the title due date form answers 

# Calender Mode 
each lead contain by default Due Date value so on click on this mode we will get something like google calender that will show the lead on weekly base where the user will choose the target week and the lead will appear based on that 
# Dynamic Project & Kanban Management System

## Overview

Create a flexible **Project Management System** where each project acts as a customizable Kanban board.

A user can:

1. Create a project.
2. Define custom form fields.
3. Define custom stages.
4. Select which field is displayed as the Kanban card title.
5. Add leads/tasks using the dynamic form.
6. Move leads/tasks between stages using drag-and-drop.

---

## Project Creation

### Basic Information

| Field | Type        | Required |
|-------|-------------|----------|
| Name  | Text        | Yes      |
| Icon  | Icon Picker | No       |

---

## Custom Form Builder

Each project must contain **at least one form field**.

These fields define the structure of every lead/task created within the project.

### Form Field Properties

| Field    | Type    | Required |
|----------|---------|----------|
| Label    | Text    | Yes      |
| Type     | Select  | Yes      |
| Required | Boolean | Yes      |

### Supported Input Types

**Basic Inputs**

- Text
- Textarea
- Number
- Email
- Phone
- URL

**Date & Time**

- Date
- Time
- DateTime

**Selection Inputs**

- Select
- Radio
- Checkbox

**Location Inputs**

- Country
- State / Province
- City
- Map Location

### Input Options

For the following field types:

- Select
- Radio
- Checkbox

The user must provide a list of options.

Example:

```json
{
  "label": "Priority",
  "type": "select",
  "required": true,
  "options": [
    "Low",
    "Medium",
    "High"
  ]
}
```

---

## Kanban Card Title Field

Since forms are dynamic, the system must know which field value should be displayed as the Kanban card title.

The user must select one of the created fields as the **Card Title Field**.

Example:

**Form Fields**

- Customer Name
- Phone
- Email
- Status

**Selected Card Title Field**

- Customer Name

**Kanban Card Display**

```
Ahmed Mohamed
```

---

## Stages

Projects may contain custom stages.

Examples:

- Backlog
- Todo
- In Progress
- Review
- Done
- Won
- Lost
- Qualified
- Contacted

Stage names are fully customizable.

### Default Stage Behavior

If the user does not create any stages, the system must automatically create:

```
Backlog
```

with:

```
sort_order = 1
```

### Stage Properties

| Field      | Type    |
|------------|---------|
| Name       | Text    |
| Sort Order | Integer |

Stages must support:

- Create
- Edit
- Delete
- Reorder
- Drag-and-drop sorting

---

## Leads / Tasks

Each project contains leads/tasks.

A lead is created by filling the project's dynamic form.

### Lead Creation Flow

**Example Form**

- Customer Name (Text)
- Phone (Phone)
- Email (Email)
- Address (Textarea)
- Priority (Select)

**Example Submission**

```
Customer Name: Ahmed Mohamed
Phone: +20123456789
Email: ahmed@test.com
Priority: High
```

The system stores all values dynamically based on the configured form fields.

### Default Stage Assignment

When a lead/task is created:

- If stages exist, assign the lead to the first stage based on `sort_order`.
- If no stages exist, automatically create `Backlog` and assign the lead to it.

Example:

```
Backlog
Todo
In Progress
Done
```

New leads are automatically assigned to:

```
Backlog
```

---

## Kanban Board

Every project has its own Kanban board.

Example:

```
+----------+ +----------+ +-------------+ +----------+
| Backlog  | | Todo     | | In Progress | | Done     |
+----------+ +----------+ +-------------+ +----------+
| Ahmed    | | Sara     | | Omar        | | John     |
| Ali      | |          | |             | |          |
+----------+ +----------+ +-------------+ +----------+
```

Card titles are displayed using the selected **Card Title Field**.

### Kanban Features

**Card Actions**

- Create Lead
- Edit Lead
- Delete Lead
- Move Lead Between Stages

**Board Actions**

- Create Stage
- Rename Stage
- Delete Stage
- Reorder Stages

**Drag & Drop**

Users can:

- Move cards between stages.
- Reorder cards inside a stage.

All changes must persist immediately.

---

## Validation Rules

### Project

```
name                => required|string|max:255
form_fields         => required|array|min:1
card_title_field_id => required
stages              => nullable|array
```

### Form Field

```
label    => required|string|max:255
type     => required|string
required => required|boolean
options  => required_if:type,select,radio,checkbox
```

### Stage

```
name       => required|string|max:255
sort_order => required|integer
```

---

## Database Structure

### projects

```
id
name
icon
card_title_field_id
created_by
created_at
updated_at
```

### project_stages

```
id
project_id
name
sort_order
created_at
updated_at
```

### project_form_fields

```
id
project_id
label
type
is_required
options       json nullable
sort_order
created_at
updated_at
```

### project_leads

```
id
project_id
project_stage_id
created_by
sort_order
created_at
updated_at
```

### project_lead_values

```
id
project_lead_id
project_form_field_id
value         json
created_at
updated_at
```

---

## Business Rules

1. Every project must contain at least one form field.
2. A form field consists of:
   - Label
   - Type
   - Required
3. Only Select, Radio, and Checkbox fields support options.
4. Every project must have a Card Title Field.
5. The Card Title Field must reference one of the project's form fields.
6. If no stages are provided, automatically create a Backlog stage.
7. Every new lead/task is assigned to the first stage by sort order.
8. Kanban cards display the value of the selected Card Title Field.
9. Drag-and-drop changes must persist immediately.
10. The architecture should be designed to support future features such as:
    - Assignees
    - Team Members
    - Comments
    - Attachments
    - Activity Logs
    - Notifications
    - CRM Pipelines
    - Automations
    - Permissions & Roles

## Members

Artmes is an API-only Laravel backend. The members feature belongs in the
`UserManagement` module because it is part of user and workspace management.

### Goal

Allow a workspace admin/owner to invite a new user to the system by email. The
admin chooses the role before sending the invitation. The invited user receives
an email with an invitation link, opens the frontend registration page, enters
their name and password, and is added to the workspace with the selected role.

### Workspace Members

Users and workspaces have a many-to-many membership relation through a
`workspace_members` table:

```text
id primary int
workspace_id foreign_id index
user_id foreign_id index
role_id foreign_id index
is_owner bool default false
created_at timestamp
updated_at timestamp
unique workspace_id + user_id
```

`workspaces.owner_id` can stay for compatibility, but authorization and access
checks should use `workspace_members` as the source of truth.

### Invitations

Create a pending invitation when an admin invites an email:

```text
id primary int
workspace_id foreign_id index
email string index
role_id foreign_id index
invited_by foreign_id index
token string unique
expires_at timestamp
accepted_at timestamp nullable
created_at timestamp
updated_at timestamp
unique workspace_id + email for pending invitations
```

Rules:

- Store only a hashed token in the database.
- Send the plain token only in the invitation email link.
- The email link points to the frontend, for example:
  `FRONTEND_URL/invitations/accept?token=...`
- The frontend calls the API to read the invitation details by token.
- The invited user accepts by sending `token`, `name`, `password`, and
  `password_confirmation`.
- For v1, invitations are for new users only. If the email already exists,
  reject the invite or acceptance with a validation error.
- After acceptance, create the user, mark the invitation as accepted, create the
  workspace membership, and return an API token.

### Roles And Permissions

Use `spatie/laravel-permission` for roles and permissions. Roles are workspace
scoped. A role has a name and a list of permission slugs. Admins create roles by
entering a role name and selecting permissions.

For this app, `create` and `update` permissions should be combined into `write`
permissions. If a user can add records, they can usually edit them too. Keep
`delete` separate because deletion is more destructive.

Suggested permissions:

```text
workspace.view
workspace.update
workspace.invite_members
workspace.manage_members
workspace.manage_roles

projects.view
projects.write
projects.delete

boards.view
boards.write

stages.view
stages.write
stages.delete

leads.view
leads.write
leads.move
leads.delete

forms.view
forms.write

fields.write
fields.delete

activity.view
```

Owners should bypass workspace permission checks.

### API Endpoints

All endpoints return JSON and use `/api/v1`.

```text
GET    /members
POST   /members/invite
GET    /members/invitations/{token}
POST   /members/invitations/register
PATCH  /members/{user}/role
DELETE /members/{user}

GET    /roles
POST   /roles
GET    /roles/{role}
PUT    /roles/{role}
PATCH  /roles/{role}
DELETE /roles/{role}

GET    /permissions
```

### Testing Requirements

- Invite a new email with an admin-selected role.
- Reject duplicate pending invitations for the same workspace/email.
- Reject invitations for existing users in v1.
- Show invitation details by valid token.
- Reject invalid, expired, or already accepted invitations.
- Register through invitation and attach the new user to the workspace role.
- List members with roles.
- Change a member role.
- Remove a member, but prevent removing the last owner.
- Create, update, list, show, and delete roles.
- List available permissions.

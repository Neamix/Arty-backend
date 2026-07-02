<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Laravel\Passport\ClientRepository;
use Modules\UserManagement\Events\WorkspaceInvitationCreated;
use Modules\UserManagement\Models\Role;
use Modules\UserManagement\Models\Workspace;
use Modules\UserManagement\Models\WorkspaceInvitation;
use Modules\UserManagement\Models\WorkspaceMember;

uses(RefreshDatabase::class);

function actingWorkspaceOwner(): array
{
    app(ClientRepository::class)->createPersonalAccessGrantClient('Test Personal Access Client', 'users');

    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $user->id]);

    test()->actingAs($user, 'api');

    WorkspaceMember::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role_id' => null,
        'is_owner' => true,
    ]);

    return [$user, $workspace];
}

it('creates a workspace role with selected permissions', function () {
    actingWorkspaceOwner();

    $response = $this->postJson('/api/v1/roles', [
        'name' => 'Manager',
        'permissions' => ['projects.view', 'projects.write'],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Manager')
        ->assertJsonPath('data.permissions.0', 'projects.view')
        ->assertJsonPath('data.permissions.1', 'projects.write');
});

it('invites a new member with the admin selected role', function () {
    [$owner, $workspace] = actingWorkspaceOwner();
    Event::fake([WorkspaceInvitationCreated::class]);

    $role = Role::create([
        'workspace_id' => $workspace->id,
        'name' => 'Sales',
        'guard_name' => 'api',
    ]);

    $response = $this->postJson('/api/v1/members/invite', [
        'email' => 'invitee@example.com',
        'role_id' => $role->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.email', 'invitee@example.com')
        ->assertJsonPath('data.role.id', $role->id);

    $this->assertDatabaseHas('workspace_invitations', [
        'workspace_id' => $workspace->id,
        'email' => 'invitee@example.com',
        'role_id' => $role->id,
        'invited_by' => $owner->id,
        'accepted_at' => null,
    ]);

    Event::assertDispatched(WorkspaceInvitationCreated::class);
});

it('registers a new user through an invitation token', function () {
    [, $workspace] = actingWorkspaceOwner();
    Event::fake([WorkspaceInvitationCreated::class]);

    $role = Role::create([
        'workspace_id' => $workspace->id,
        'name' => 'Agent',
        'guard_name' => 'api',
    ]);

    $this->postJson('/api/v1/members/invite', [
        'email' => 'new-user@example.com',
        'role_id' => $role->id,
    ])->assertCreated();

    $plainToken = null;

    Event::assertDispatched(WorkspaceInvitationCreated::class, function (WorkspaceInvitationCreated $event) use (&$plainToken) {
        $query = parse_url($event->invitationUrl, PHP_URL_QUERY);
        parse_str((string) $query, $parameters);
        $plainToken = $parameters['token'] ?? null;

        return is_string($plainToken);
    });

    $response = $this->postJson('/api/v1/members/invitations/register', [
        'token' => $plainToken,
        'name' => 'New User',
        'password' => 'Password1',
        'password_confirmation' => 'Password1',
    ]);

    $response->assertCreated()
        ->assertJsonPath('user.email', 'new-user@example.com');

    $createdUser = User::where('email', 'new-user@example.com')->firstOrFail();

    $this->assertDatabaseHas('workspace_members', [
        'workspace_id' => $workspace->id,
        'user_id' => $createdUser->id,
        'role_id' => $role->id,
        'is_owner' => false,
    ]);

    $this->assertDatabaseMissing('workspace_invitations', [
        'workspace_id' => $workspace->id,
        'email' => 'new-user@example.com',
        'accepted_at' => null,
    ]);
});

it('prevents removing the last workspace owner', function () {
    [$owner] = actingWorkspaceOwner();

    $this->deleteJson("/api/v1/members/{$owner->id}")
        ->assertUnprocessable();
});

it('forbids non-owners from inviting members', function () {
    [, $workspace] = actingWorkspaceOwner();

    $member = User::factory()->create();
    WorkspaceMember::create([
        'workspace_id' => $workspace->id,
        'user_id' => $member->id,
        'role_id' => null,
        'is_owner' => false,
    ]);
    test()->actingAs($member, 'api');

    $role = Role::create(['workspace_id' => $workspace->id, 'name' => 'Sales', 'guard_name' => 'api']);

    $this->postJson('/api/v1/members/invite', [
        'email' => 'someone@example.com',
        'role_id' => $role->id,
    ])->assertForbidden();
});

it('rejects inviting an email that belongs to an existing user', function () {
    [$owner, $workspace] = actingWorkspaceOwner();

    $role = Role::create(['workspace_id' => $workspace->id, 'name' => 'Sales', 'guard_name' => 'api']);

    $this->postJson('/api/v1/members/invite', [
        'email' => $owner->email,
        'role_id' => $role->id,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('rejects inviting an email that already has a pending invitation', function () {
    [, $workspace] = actingWorkspaceOwner();
    Event::fake([WorkspaceInvitationCreated::class]);

    $role = Role::create(['workspace_id' => $workspace->id, 'name' => 'Sales', 'guard_name' => 'api']);

    $this->postJson('/api/v1/members/invite', [
        'email' => 'pending@example.com',
        'role_id' => $role->id,
    ])->assertCreated();

    $this->postJson('/api/v1/members/invite', [
        'email' => 'pending@example.com',
        'role_id' => $role->id,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('rejects a role belonging to another workspace', function () {
    actingWorkspaceOwner();

    $otherOwner = User::factory()->create();
    $otherWorkspace = Workspace::factory()->create(['owner_id' => $otherOwner->id]);
    $foreignRole = Role::create(['workspace_id' => $otherWorkspace->id, 'name' => 'Spy', 'guard_name' => 'api']);

    $this->postJson('/api/v1/members/invite', [
        'email' => 'someone@example.com',
        'role_id' => $foreignRole->id,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('role_id');
});

it('rejects an expired invitation token', function () {
    [$owner, $workspace] = actingWorkspaceOwner();

    $role = Role::create(['workspace_id' => $workspace->id, 'name' => 'Sales', 'guard_name' => 'api']);
    $plainToken = Str::random(64);

    WorkspaceInvitation::create([
        'workspace_id' => $workspace->id,
        'email' => 'expired@example.com',
        'role_id' => $role->id,
        'invited_by' => $owner->id,
        'token' => hash('sha256', $plainToken),
        'expires_at' => now()->subDay(),
    ]);

    $this->getJson("/api/v1/members/invitations/{$plainToken}")
        ->assertUnprocessable()
        ->assertJsonValidationErrors('token');
});

it('rejects an already accepted invitation token', function () {
    [$owner, $workspace] = actingWorkspaceOwner();

    $role = Role::create(['workspace_id' => $workspace->id, 'name' => 'Sales', 'guard_name' => 'api']);
    $plainToken = Str::random(64);

    $invitation = WorkspaceInvitation::create([
        'workspace_id' => $workspace->id,
        'email' => 'accepted@example.com',
        'role_id' => $role->id,
        'invited_by' => $owner->id,
        'token' => hash('sha256', $plainToken),
        'expires_at' => now()->addDay(),
    ]);
    $invitation->forceFill(['accepted_at' => now()])->save();

    $this->postJson('/api/v1/members/invitations/register', [
        'token' => $plainToken,
        'name' => 'Late User',
        'password' => 'Password1',
        'password_confirmation' => 'Password1',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('token');
});

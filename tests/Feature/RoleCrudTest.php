<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Modules\UserManagement\Models\Role;
use Modules\UserManagement\Models\Workspace;
use Modules\UserManagement\Models\WorkspaceMember;

uses(RefreshDatabase::class);

function roleTestOwner(): array
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

function roleTestMember(Workspace $workspace, ?int $roleId = null): User
{
    $member = User::factory()->create();

    WorkspaceMember::create([
        'workspace_id' => $workspace->id,
        'user_id' => $member->id,
        'role_id' => $roleId,
        'is_owner' => false,
    ]);

    return $member;
}

it('lists workspace roles', function () {
    [, $workspace] = roleTestOwner();

    Role::create(['workspace_id' => $workspace->id, 'name' => 'Sales', 'guard_name' => 'api']);

    $this->getJson('/api/v1/roles')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Sales');
});

it('shows a single role', function () {
    [, $workspace] = roleTestOwner();

    $role = Role::create(['workspace_id' => $workspace->id, 'name' => 'Sales', 'guard_name' => 'api']);

    $this->getJson("/api/v1/roles/{$role->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $role->id);
});

it('creates a role with permissions', function () {
    roleTestOwner();

    $this->postJson('/api/v1/roles', [
        'name' => 'Manager',
        'permissions' => ['projects.view', 'projects.write'],
    ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Manager')
        ->assertJsonPath('data.permissions.0', 'projects.view');
});

it('rejects a duplicate role name in the same workspace', function () {
    [, $workspace] = roleTestOwner();

    Role::create(['workspace_id' => $workspace->id, 'name' => 'Manager', 'guard_name' => 'api']);

    $this->postJson('/api/v1/roles', [
        'name' => 'Manager',
        'permissions' => [],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('updates a role name and permissions', function () {
    [, $workspace] = roleTestOwner();

    $role = Role::create(['workspace_id' => $workspace->id, 'name' => 'Sales', 'guard_name' => 'api']);

    $this->putJson("/api/v1/roles/{$role->id}", [
        'name' => 'Sales Lead',
        'permissions' => ['leads.view'],
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Sales Lead')
        ->assertJsonPath('data.permissions.0', 'leads.view');
});

it('deletes an unassigned role', function () {
    [, $workspace] = roleTestOwner();

    $role = Role::create(['workspace_id' => $workspace->id, 'name' => 'Sales', 'guard_name' => 'api']);

    $this->deleteJson("/api/v1/roles/{$role->id}")->assertOk();

    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});

it('blocks deleting a role assigned to a member', function () {
    [, $workspace] = roleTestOwner();

    $role = Role::create(['workspace_id' => $workspace->id, 'name' => 'Sales', 'guard_name' => 'api']);
    roleTestMember($workspace, $role->id);

    $this->deleteJson("/api/v1/roles/{$role->id}")
        ->assertUnprocessable()
        ->assertJsonValidationErrors('role');

    $this->assertDatabaseHas('roles', ['id' => $role->id]);
});

it('forbids non-owners from managing roles', function () {
    [, $workspace] = roleTestOwner();

    $member = roleTestMember($workspace);
    test()->actingAs($member, 'api');

    $this->getJson('/api/v1/roles')->assertForbidden();

    $this->postJson('/api/v1/roles', [
        'name' => 'Hacker',
        'permissions' => [],
    ])->assertForbidden();
});

it('returns grouped permissions to owners', function () {
    roleTestOwner();

    $this->getJson('/api/v1/permissions')
        ->assertOk()
        ->assertJsonStructure(['data' => ['workspace', 'projects']]);
});

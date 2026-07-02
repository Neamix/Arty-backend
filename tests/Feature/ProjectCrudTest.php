<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ProjectManagment\Models\Project;
use Modules\UserManagement\Models\Workspace;
use Modules\UserManagement\Models\WorkspaceMember;

uses(RefreshDatabase::class);

function actingUserWithWorkspace(): array
{
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

it('lists only projects within the user workspaces', function () {
    [$user, $workspace] = actingUserWithWorkspace();

    Project::create(['workspace_id' => $workspace->id, 'title' => 'Mine A']);
    Project::create(['workspace_id' => $workspace->id, 'title' => 'Mine B']);

    $otherWorkspace = Workspace::factory()->create();
    Project::create(['workspace_id' => $otherWorkspace->id, 'title' => 'Theirs']);

    $response = $this->getJson('/api/v1/projects');

    $response->assertOk()->assertJsonCount(2, 'data');
});

it('filters projects by title', function () {
    [$user, $workspace] = actingUserWithWorkspace();

    Project::create(['workspace_id' => $workspace->id, 'title' => 'Apollo']);
    Project::create(['workspace_id' => $workspace->id, 'title' => 'Gemini']);

    $response = $this->getJson('/api/v1/projects?title=Apol');

    $response->assertOk()->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Apollo');
});

it('creates a project assigned to the user workspace', function () {
    [$user, $workspace] = actingUserWithWorkspace();

    $response = $this->postJson('/api/v1/projects', [
        'title' => 'New Project',
    ]);

    $response->assertCreated()->assertJsonPath('data.title', 'New Project');

    $this->assertDatabaseHas('projects', [
        'workspace_id' => $workspace->id,
        'title' => 'New Project',
    ]);
});

it('ignores a client supplied workspace_id and uses the user workspace', function () {
    [$user, $workspace] = actingUserWithWorkspace();

    $otherWorkspace = Workspace::factory()->create();

    $this->postJson('/api/v1/projects', [
        'title' => 'Forced',
        'workspace_id' => $otherWorkspace->id,
    ])->assertCreated();

    $this->assertDatabaseHas('projects', [
        'workspace_id' => $workspace->id,
        'title' => 'Forced',
    ]);

    $this->assertDatabaseMissing('projects', [
        'workspace_id' => $otherWorkspace->id,
        'title' => 'Forced',
    ]);
});

it('keeps a project in the user workspace on update', function () {
    [$user, $workspace] = actingUserWithWorkspace();

    $project = Project::create(['workspace_id' => $workspace->id, 'title' => 'Stay']);

    $this->putJson("/api/v1/projects/{$project->id}", ['title' => 'Renamed'])
        ->assertOk();

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'workspace_id' => $workspace->id,
        'title' => 'Renamed',
    ]);
});

it('shows an owned project', function () {
    [$user, $workspace] = actingUserWithWorkspace();

    $project = Project::create(['workspace_id' => $workspace->id, 'title' => 'Visible']);

    $this->getJson("/api/v1/projects/{$project->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $project->id);
});

it('hides a project from another workspace', function () {
    [$user, $workspace] = actingUserWithWorkspace();

    $otherWorkspace = Workspace::factory()->create();
    $foreign = Project::create(['workspace_id' => $otherWorkspace->id, 'title' => 'Hidden']);

    $this->getJson("/api/v1/projects/{$foreign->id}")->assertNotFound();
});

it('updates an owned project', function () {
    [$user, $workspace] = actingUserWithWorkspace();

    $project = Project::create(['workspace_id' => $workspace->id, 'title' => 'Old']);

    $this->putJson("/api/v1/projects/{$project->id}", ['title' => 'Updated'])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated');

    $this->assertDatabaseHas('projects', ['id' => $project->id, 'title' => 'Updated']);
});

it('deletes an owned project', function () {
    [$user, $workspace] = actingUserWithWorkspace();

    $project = Project::create(['workspace_id' => $workspace->id, 'title' => 'Doomed']);

    $this->deleteJson("/api/v1/projects/{$project->id}")->assertOk();

    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
});

it('requires authentication', function () {
    $this->getJson('/api/v1/projects')->assertUnauthorized();
});

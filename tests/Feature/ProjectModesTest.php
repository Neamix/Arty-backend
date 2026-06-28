<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Modules\ProjectManagment\Enums\FieldType;
use Modules\ProjectManagment\Models\Field;
use Modules\ProjectManagment\Models\Form;
use Modules\ProjectManagment\Models\Lead;
use Modules\ProjectManagment\Models\LeadAnswer;
use Modules\ProjectManagment\Models\Project;
use Modules\ProjectManagment\Models\Stage;
use Modules\UserManagement\Models\Workspace;

uses(RefreshDatabase::class);

function actingProjectModeUserWithWorkspace(): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $user->id]);

    test()->actingAs($user, 'api');

    return [$user, $workspace];
}

function createProjectModeLeadFixture(Workspace $workspace): array
{
    $project = Project::create([
        'workspace_id' => $workspace->id,
        'title' => 'Pipeline',
    ]);

    $form = Form::create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'name' => 'Intake',
    ]);

    $name = Field::create([
        'workspace_id' => $workspace->id,
        'form_id' => $form->id,
        'label' => 'Name',
        'type' => FieldType::Text,
        'is_required' => true,
        'sort_order' => 1,
        'default_value' => 'Untitled',
        'is_title' => true,
    ]);

    $email = Field::create([
        'workspace_id' => $workspace->id,
        'form_id' => $form->id,
        'label' => 'Email',
        'type' => FieldType::Text,
        'is_required' => false,
        'sort_order' => 2,
    ]);

    $stage = Stage::create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'name' => 'Drafted',
        'sort_order' => 1,
    ]);

    $lead = Lead::create([
        'workspace_id' => $workspace->id,
        'stage_id' => $stage->id,
        'due_date' => Carbon::parse('2026-06-30 10:00:00'),
    ]);

    LeadAnswer::create([
        'workspace_id' => $workspace->id,
        'lead_id' => $lead->id,
        'field_id' => $name->id,
        'value' => 'Acme Lead',
    ]);

    LeadAnswer::create([
        'workspace_id' => $workspace->id,
        'lead_id' => $lead->id,
        'field_id' => $email->id,
        'value' => 'lead@example.com',
    ]);

    return [$project, $stage, $lead, $name, $email];
}

it('returns project leads in sheet mode', function () {
    [$user, $workspace] = actingProjectModeUserWithWorkspace();
    [$project, $stage, $lead, $name, $email] = createProjectModeLeadFixture($workspace);

    $response = $this->getJson("/api/v1/projects/{$project->id}/sheet");

    $response->assertOk()
        ->assertJsonPath('data.mode', 'sheet')
        ->assertJsonPath('data.leads.data.0.id', $lead->id)
        ->assertJsonPath('data.leads.data.0.answers.0.value', 'Acme Lead')
        ->assertJsonPath('data.leads.data.0.answers.1.value', 'lead@example.com');
});

it('does not create a form when reading sheet mode', function () {
    [$user, $workspace] = actingProjectModeUserWithWorkspace();

    $project = Project::create([
        'workspace_id' => $workspace->id,
        'title' => 'No Form Yet',
    ]);

    $this->getJson("/api/v1/projects/{$project->id}/sheet")
        ->assertOk()
        ->assertJsonCount(0, 'data.leads.data');

    $this->assertDatabaseMissing('forms', [
        'project_id' => $project->id,
    ]);
});

it('returns kanban mode with capped stage columns and lead card data', function () {
    [$user, $workspace] = actingProjectModeUserWithWorkspace();
    [$project, $stage, $lead] = createProjectModeLeadFixture($workspace);

    $response = $this->getJson("/api/v1/projects/{$project->id}/kanban");

    $response->assertOk()
        ->assertJsonPath('data.mode', 'kanban')
        ->assertJsonPath('data.stages.0.id', $stage->id)
        ->assertJsonPath('data.stages.0.leads.0.id', $lead->id)
        ->assertJsonPath('data.stages.0.leads.0.title', 'Acme Lead')
        ->assertJsonPath('data.stages.0.leads.0.due_date', '2026-06-30T10:00:00+00:00');
});

it('does not create a form when reading kanban mode', function () {
    [$user, $workspace] = actingProjectModeUserWithWorkspace();

    $project = Project::create([
        'workspace_id' => $workspace->id,
        'title' => 'Kanban Without Form',
    ]);

    Stage::create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'name' => 'Drafted',
        'sort_order' => 1,
    ]);

    $this->getJson("/api/v1/projects/{$project->id}/kanban")
        ->assertOk()
        ->assertJsonPath('data.mode', 'kanban')
        ->assertJsonCount(1, 'data.stages');

    $this->assertDatabaseMissing('forms', [
        'project_id' => $project->id,
    ]);
});

it('returns calendar mode for the selected week only', function () {
    [$user, $workspace] = actingProjectModeUserWithWorkspace();
    [$project, $stage] = createProjectModeLeadFixture($workspace);

    Lead::create([
        'workspace_id' => $workspace->id,
        'stage_id' => $stage->id,
        'due_date' => Carbon::parse('2026-07-10 10:00:00'),
    ]);

    $response = $this->getJson("/api/v1/projects/{$project->id}/calendar?week_start=2026-06-29");

    $response->assertOk()
        ->assertJsonPath('data.mode', 'calendar')
        ->assertJsonPath('data.week.starts_at', '2026-06-29')
        ->assertJsonPath('data.week.ends_at', '2026-07-05')
        ->assertJsonCount(1, 'data.leads.data')
        ->assertJsonPath('data.leads.data.0.due_date', '2026-06-30T10:00:00.000000Z')
        ->assertJsonPath('data.leads.data.0.answers.0.value', 'Acme Lead');
});

it('hides project mode data from other workspaces', function () {
    [$user, $workspace] = actingProjectModeUserWithWorkspace();

    $otherWorkspace = Workspace::factory()->create();
    [$foreignProject] = createProjectModeLeadFixture($otherWorkspace);

    $this->getJson("/api/v1/projects/{$foreignProject->id}/sheet")->assertNotFound();
    $this->getJson("/api/v1/projects/{$foreignProject->id}/calendar?week_start=2026-06-29")->assertNotFound();
});

<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ProjectManagement\Models\Lead;
use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Models\Stage;
use Modules\ProjectManagement\Repositories\LeadRepository;
use Modules\ProjectManagement\Services\LeadService;

uses(RefreshDatabase::class);

function makeStage(): Stage
{
    $project = Project::factory()->create();

    return Stage::create([
        'project_id' => $project->id,
        'name' => 'Backlog',
        'sort_order' => 1,
    ]);
}

function makeLead(Stage $stage, int $sortOrder): Lead
{
    return Lead::create([
        'project_id' => $stage->project_id,
        'stage_id' => $stage->id,
        'created_by' => User::factory()->create()->id,
        'sort_order' => $sortOrder,
    ]);
}

it('spaces appended leads by the sort gap', function () {
    $stage = makeStage();
    $repo = app(LeadRepository::class);

    expect($repo->nextSortOrder($stage->id))->toBe(100000);

    makeLead($stage, 100000);
    expect($repo->nextSortOrder($stage->id))->toBe(200000);

    makeLead($stage, 200000);
    expect($repo->nextSortOrder($stage->id))->toBe(300000);
});

it('averages neighbours when dropping between two cards', function () {
    $stage = makeStage();
    $first = makeLead($stage, 100000);
    $second = makeLead($stage, 200000);
    $moved = makeLead($stage, 300000);

    app(LeadService::class)->move($moved, $stage->project, $moved->creator, $stage->id, $first->id, $second->id);

    expect($moved->fresh()->sort_order)->toBe(150000);
});

it('halves the first order when dropping at the top', function () {
    $stage = makeStage();
    $first = makeLead($stage, 100000);
    $moved = makeLead($stage, 200000);

    app(LeadService::class)->move($moved, $stage->project, $moved->creator, $stage->id, null, $first->id);

    expect($moved->fresh()->sort_order)->toBe(50000);
});

it('appends past the last order when dropping at the bottom', function () {
    $stage = makeStage();
    makeLead($stage, 100000);
    $last = makeLead($stage, 200000);
    $moved = makeLead($stage, 300000);

    app(LeadService::class)->move($moved, $stage->project, $moved->creator, $stage->id, $last->id, null);

    expect($moved->fresh()->sort_order)->toBe(300000);
});

it('rebalances the stage when the neighbour gap is too small', function () {
    $stage = makeStage();
    $first = makeLead($stage, 100000);
    $second = makeLead($stage, 110000);
    $third = makeLead($stage, 120000);
    $moved = makeLead($stage, 130000);

    app(LeadService::class)->move($moved, $stage->project, $moved->creator, $stage->id, $first->id, $second->id);

    expect($first->fresh()->sort_order)->toBe(100000);
    expect($second->fresh()->sort_order)->toBe(200000);
    expect($third->fresh()->sort_order)->toBe(300000);
    expect($moved->fresh()->sort_order)->toBe(150000);

    $orders = Lead::where('stage_id', $stage->id)
        ->pluck('sort_order');
    expect($orders->count())->toBe($orders->unique()->count());
});

it('moves a lead into another stage between its neighbours', function () {
    $source = makeStage();
    $target = Stage::create([
        'project_id' => $source->project_id,
        'name' => 'Done',
        'sort_order' => 2,
    ]);

    $moved = makeLead($source, 100000);
    $a = makeLead($target, 100000);
    $b = makeLead($target, 200000);

    app(LeadService::class)->move($moved, $source->project, $moved->creator, $target->id, $a->id, $b->id);

    expect($moved->fresh()->stage_id)->toBe($target->id);
    expect($moved->fresh()->sort_order)->toBe(150000);
});

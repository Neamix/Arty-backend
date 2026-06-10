<?php

namespace Modules\ProjectManagement\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\ProjectManagement\Models\Project;

class ProjectRepository
{
    public function __construct(private Project $project) {}

    public function forUser(int $userId): Collection
    {
        return $this->project->newQuery()
            ->where('created_by', $userId)
            ->withCount('stages')
            ->latest()
            ->get();
    }

    public function incrementLeadCount(Project $project): void
    {
        $project->increment('lead_count');
    }

    public function decrementLeadCount(Project $project): void
    {
        $project->decrement('lead_count');
    }

    public function create(array $attributes): Project
    {
        return $this->project->newQuery()->create($attributes);
    }

    public function update(Project $project, array $attributes): Project
    {
        $project->update($attributes);

        return $project;
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }

    public function loadBoard(Project $project, int $leadsPerStage = 30): Project
    {
        return $project->load([
            'formFields',
            'cardTitleField',
            'stages' => fn ($query) => $query->withCount('leads'),
            'stages.leads' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')->limit($leadsPerStage),
            'stages.leads.values',
        ]);
    }

    public function loadDetails(Project $project): Project
    {
        return $project->load(['formFields', 'stages', 'cardTitleField']);
    }
}

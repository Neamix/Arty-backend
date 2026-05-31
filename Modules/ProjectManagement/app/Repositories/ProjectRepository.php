<?php

namespace Modules\ProjectManagement\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\ProjectManagement\Models\Project;

class ProjectRepository
{
    public function __construct(private Project $project) {}

    /**
     * @return Collection<int, Project>
     */
    public function forUser(int $userId): Collection
    {
        return $this->project->newQuery()
            ->where('created_by', $userId)
            ->withCount(['stages', 'leads'])
            ->latest()
            ->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Project
    {
        return $this->project->newQuery()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Project $project, array $attributes): Project
    {
        $project->update($attributes);

        return $project;
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }

    public function loadBoard(Project $project): Project
    {
        return $project->load([
            'formFields',
            'cardTitleField',
            'stages.leads.values',
        ]);
    }

    public function loadDetails(Project $project): Project
    {
        return $project->load(['formFields', 'stages', 'cardTitleField']);
    }
}

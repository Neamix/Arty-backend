<?php

namespace Modules\ProjectManagment\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\ProjectManagment\Models\Project;

class ProjectRepository
{
    public function __construct(private Project $project) {}

    public function filter(array $filters): Collection
    {
        return $this->project->newQuery()
            ->filter($filters)
            ->latest()
            ->get();
    }

    public function find(int $id): Project
    {
        return $this->project->newQuery()->findOrFail($id);
    }

    public function create(array $data): Project
    {
        return $this->project->newQuery()->create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->refresh();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}

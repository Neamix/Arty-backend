<?php

namespace Modules\ProjectManagement\Repositories;

use Illuminate\Support\Facades\Date;
use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Models\ProjectStage;

class ProjectStageRepository
{
    public function __construct(private ProjectStage $stage) {}

    public function createForProject(Project $project, array $attributes): ProjectStage
    {
        return $project->stages()->create($attributes);
    }

    public function createManyForProject(Project $project, array $stages): void
    {
        $now = Date::now();

        $rows = [];

        foreach (array_values($stages) as $index => $stage) {
            $rows[] = [
                'project_id' => $project->id,
                'name' => $stage['name'],
                'sort_order' => $stage['sort_order'] ?? ($index + 1),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->stage->newQuery()->insert($rows);
    }

    public function update(ProjectStage $stage, array $attributes): ProjectStage
    {
        $stage->update($attributes);

        return $stage;
    }

    public function delete(ProjectStage $stage): void
    {
        $stage->delete();
    }

    public function firstBySortOrder(int $projectId): ?ProjectStage
    {
        return $this->stage->newQuery()
            ->where('project_id', $projectId)
            ->orderBy('sort_order')
            ->first();
    }

    public function countForProject(int $projectId): int
    {
        return $this->stage->newQuery()
            ->where('project_id', $projectId)
            ->count();
    }
}

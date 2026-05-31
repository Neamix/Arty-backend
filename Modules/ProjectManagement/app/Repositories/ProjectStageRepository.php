<?php

namespace Modules\ProjectManagement\Repositories;

use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Models\ProjectStage;

class ProjectStageRepository
{
    public function __construct(private ProjectStage $stage) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createForProject(Project $project, array $attributes): ProjectStage
    {
        return $project->stages()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
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

    public function belongsToProject(int $projectId, int $stageId): bool
    {
        return $this->stage->newQuery()
            ->where('project_id', $projectId)
            ->whereKey($stageId)
            ->exists();
    }
}

<?php

namespace Modules\ProjectManagement\Repositories;

use Illuminate\Support\Facades\Date;
use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Models\Stage;

class StageRepository
{
    public function __construct(private Stage $stage) {}

    public function createForProject(Project $project, array $attributes): Stage
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

    public function update(Stage $stage, array $attributes): Stage
    {
        $stage->update($attributes);

        return $stage;
    }

    public function delete(Stage $stage): void
    {
        $stage->delete();
    }

    public function reorderForProject(Project $project, array $orderedStageIds): void
    {
        $cases = [];

        foreach (array_values($orderedStageIds) as $index => $stageId) {
            $cases[] = 'WHEN '.(int) $stageId.' THEN '.($index + 1);
        }

        $project->stages()
            ->whereIn('id', $orderedStageIds)
            ->update(['sort_order' => $this->stage->getConnection()->raw('CASE id '.implode(' ', $cases).' END')]);
    }

    public function firstBySortOrder(int $projectId): ?Stage
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

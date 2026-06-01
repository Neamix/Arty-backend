<?php

namespace Modules\ProjectManagement\Services;

use Illuminate\Support\Facades\DB;
use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Models\ProjectStage;
use Modules\ProjectManagement\Repositories\ProjectStageRepository;

class ProjectStageService
{
    public function __construct(private ProjectStageRepository $stageRepository) {}

    public function create(Project $project, array $data): ProjectStage
    {
        $sortOrder = $data['sort_order']
            ?? ($this->stageRepository->countForProject($project->id) + 1);

        return $this->stageRepository->createForProject($project, [
            'name' => $data['name'],
            'sort_order' => $sortOrder,
        ]);
    }

    public function update(ProjectStage $stage, array $data): ProjectStage
    {
        return $this->stageRepository->update($stage, $data);
    }

    public function delete(ProjectStage $stage): void
    {
        $this->stageRepository->delete($stage);
    }

    public function reorder(Project $project, array $orderedStageIds): void
    {
        DB::transaction(function () use ($project, $orderedStageIds): void {
            foreach (array_values($orderedStageIds) as $index => $stageId) {
                $project->stages()
                    ->whereKey($stageId)
                    ->update(['sort_order' => $index + 1]);
            }
        });
    }
}

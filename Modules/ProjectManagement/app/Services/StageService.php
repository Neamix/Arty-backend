<?php

namespace Modules\ProjectManagement\Services;

use Illuminate\Support\Facades\DB;
use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Models\Stage;
use Modules\ProjectManagement\Repositories\StageRepository;

class StageService
{
    public function __construct(private StageRepository $stageRepository) {}

    public function create(Project $project, array $data): Stage
    {
        $sortOrder = $data['sort_order']
            ?? ($this->stageRepository->countForProject($project->id) + 1);

        return $this->stageRepository->createForProject($project, [
            'name' => $data['name'],
            'sort_order' => $sortOrder,
        ]);
    }

    public function update(Stage $stage, array $data): Stage
    {
        return $this->stageRepository->update($stage, $data);
    }

    public function delete(Stage $stage): void
    {
        $this->stageRepository->delete($stage);
    }

    public function reorder(Project $project, array $orderedStageIds): void
    {
        DB::transaction(function () use ($project, $orderedStageIds): void {
            $this->stageRepository->reorderForProject($project, $orderedStageIds);
        });
    }
}

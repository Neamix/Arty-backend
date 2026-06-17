<?php

namespace Modules\ProjectManagment\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\ProjectManagment\Models\Project;
use Modules\ProjectManagment\Models\Stage;
use Modules\ProjectManagment\Repositories\ProjectRepository;
use Modules\ProjectManagment\Repositories\StageRepository;

class StageService
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private StageRepository $stageRepository,
    ) {}

    public function filter(int $projectId, array $filters): Collection
    {
        $project = $this->projectRepository->find($projectId);

        return $this->stageRepository->filter([...$filters, 'project_id' => $project->id]);
    }

    public function find(int $projectId, int $stageId): Stage
    {
        $project = $this->projectRepository->find($projectId);

        return $this->findForProject($project, $stageId);
    }

    public function create(int $projectId, array $data): Stage
    {
        $project = $this->projectRepository->find($projectId);

        return $this->stageRepository->create([
            'project_id' => $project->id,
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? $this->stageRepository->nextSortOrder($project->id),
        ]);
    }

    public function update(int $projectId, int $stageId, array $data): Stage
    {
        $project = $this->projectRepository->find($projectId);
        $stage = $this->findForProject($project, $stageId);

        return $this->stageRepository->update($stage, $data);
    }

    public function delete(int $projectId, int $stageId): void
    {
        $project = $this->projectRepository->find($projectId);
        $stage = $this->findForProject($project, $stageId);

        $this->stageRepository->delete($stage);
    }

    private function findForProject(Project $project, int $stageId): Stage
    {
        $stage = $this->stageRepository->find($stageId);

        if ($stage->project_id !== $project->id) {
            throw new ModelNotFoundException;
        }

        return $stage;
    }
}

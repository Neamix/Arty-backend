<?php

namespace Modules\ProjectManagment\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\ProjectManagment\Models\Project;
use Modules\ProjectManagment\Repositories\FormRepository;
use Modules\ProjectManagment\Repositories\ProjectRepository;
use Modules\ProjectManagment\Repositories\StageRepository;

class ProjectService
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private FormRepository $formRepository,
        private StageRepository $stageRepository,
    ) {}

    public function filter(array $filters): Collection
    {
        return $this->projectRepository->filter($filters);
    }

    public function find(int $id): Project
    {
        return $this->projectRepository->find($id);
    }

    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $project = $this->projectRepository->create($data);
            $this->formRepository->create(['project_id' => $project->id]);
            $this->stageRepository->create(['project_id' => $project->id, 'name' => 'drafted', 'sort_order' => 1]);

            return $project;
        });
    }

    public function update(int $id, array $data): Project
    {
        $project = $this->projectRepository->find($id);

        return $this->projectRepository->update($project, $data);
    }

    public function delete(int $id): void
    {
        $project = $this->projectRepository->find($id);

        $this->projectRepository->delete($project);
    }
}

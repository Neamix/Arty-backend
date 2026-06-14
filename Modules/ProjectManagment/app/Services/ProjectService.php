<?php

namespace Modules\ProjectManagment\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\ProjectManagment\Models\Project;
use Modules\ProjectManagment\Repositories\ProjectRepository;

class ProjectService
{
    public function __construct(private ProjectRepository $projectRepository) {}

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
        return $this->projectRepository->create($data);
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

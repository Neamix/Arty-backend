<?php

namespace Modules\ProjectManagment\Repositories;

use Modules\ProjectManagment\Models\Project;

class ProjectRepository
{
    public function __construct(protected Project $project) {}

    public function create(array $data) {
        return $this->project->create($data);
    }

    public function update(int $id,array $data): Project
    {
        $project = $this->find($id);
        $project->update($data);

        return $project->refresh(); 
    }

    public function find(int $id): Project
    {
        return $this->project->find($id);
    }

    public function filter(array $data)
    {
        return $this->project->filter($data);
    }
}

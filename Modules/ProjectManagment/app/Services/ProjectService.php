<?php

namespace Modules\ProjectManagment\Services;

use Modules\ProjectManagment\Repositories\ProjectRepository;

class ProjectService
{
    public function __construct(private ProjectRepository $projectRepository) {}

    public function create(array $projetCreateArray): array
    {
        $project = $this->projectRepository->create($projetCreateArray);

        return [
            'status'  => 'success',
            'project' => $project
        ];
    }
}

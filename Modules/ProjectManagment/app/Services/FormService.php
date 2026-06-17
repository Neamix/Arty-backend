<?php

namespace Modules\ProjectManagment\Services;

use Modules\ProjectManagment\Models\Form;
use Modules\ProjectManagment\Repositories\FormRepository;
use Modules\ProjectManagment\Repositories\ProjectRepository;

class FormService
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private FormRepository $formRepository,
    ) {}


    public function find(int $projectId): Form
    {
        return $this->formRepository->find($projectId);
    }

    public function update(int $projectId, array $data): Form
    {
        $form = $this->find($projectId);

        return $this->formRepository->update($form, $data);
    }
}

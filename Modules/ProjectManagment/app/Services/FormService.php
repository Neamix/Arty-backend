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

    public function find(int $id): Form
    {
        return $this->formRepository->find($id);
    }

    public function create(array $data): Form
    {
        return $this->formRepository->create($data);
    }

    public function update(int $id, array $data): Form
    {
        $form = $this->formRepository->find($id);

        return $this->formRepository->update($form, $data);
    }
}

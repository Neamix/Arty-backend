<?php

namespace Modules\ProjectManagment\Repositories;

use Modules\ProjectManagment\Models\Form;

class FormRepository
{
    public function __construct(private Form $form) {}

    public function find(int $id): Form
    {
        return $this->form->findOrFail($id);
    }

    public function firstOrCreateForProject(int $projectId): Form
    {
        return $this->form->firstOrCreate(['project_id' => $projectId]);
    }

    public function create(array $data): Form
    {
        return $this->form->create($data);
    }

    public function update(Form $form, array $data): Form
    {
        $form->update($data);

        return $form->refresh();
    }
}

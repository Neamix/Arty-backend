<?php

namespace Modules\ProjectManagment\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\ProjectManagment\Models\Field;
use Modules\ProjectManagment\Models\Form;
use Modules\ProjectManagment\Repositories\FieldRepository;

class FieldService
{
    public function __construct(
        private FormService $formService,
        private FieldRepository $fieldRepository,
        private FieldOptionService $fieldOptionService,
    ) {}

    public function filter(int $projectId, array $filters): Collection
    {
        $form = $this->formService->resolveForProject($projectId);

        return $this->fieldRepository->filter([...$filters, 'form_id' => $form->id]);
    }

    public function find(int $projectId, int $fieldId): Field
    {
        $form = $this->formService->resolveForProject($projectId);

        return $this->findForForm($form, $fieldId)->load('options');
    }

    public function create(int $projectId, array $data): Field
    {
        $form = $this->formService->resolveForProject($projectId);

        return DB::transaction(function () use ($form, $data) {
            $field = $this->fieldRepository->create([
                'form_id' => $form->id,
                'label' => $data['label'],
                'type' => $data['type'],
                'is_required' => $data['is_required'] ?? false,
                'sort_order' => $data['sort_order'] ?? $this->fieldRepository->nextSortOrder($form->id),
                'config' => $data['config'] ?? null,
            ]);

            if (! empty($data['options'])) {
                $this->fieldOptionService->sync($field, $data['options']);
            }

            return $field->load('options');
        });
    }

    public function update(int $projectId, int $fieldId, array $data): Field
    {
        $form = $this->formService->resolveForProject($projectId);
        $field = $this->findForForm($form, $fieldId);

        return DB::transaction(function () use ($field, $data) {
            $field = $this->fieldRepository->update($field, Arr::only($data, ['label', 'type', 'is_required', 'sort_order', 'config']));

            if (array_key_exists('options', $data)) {
                $this->fieldOptionService->sync($field, $data['options'] ?? []);
            }

            return $field->load('options');
        });
    }

    public function delete(int $projectId, int $fieldId): void
    {
        $form = $this->formService->resolveForProject($projectId);
        $field = $this->findForForm($form, $fieldId);

        $this->fieldRepository->delete($field);
    }

    private function findForForm(Form $form, int $fieldId): Field
    {
        $field = $this->fieldRepository->find($fieldId);

        if ($field->form_id !== $form->id) {
            throw new ModelNotFoundException;
        }

        return $field;
    }
}

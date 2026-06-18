<?php

namespace Modules\ProjectManagment\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Modules\ProjectManagment\Models\Field;
use Modules\ProjectManagment\Models\FieldOption;
use Modules\ProjectManagment\Repositories\FieldOptionRepository;
use Modules\ProjectManagment\Repositories\FieldRepository;

class FieldOptionService
{
    public function __construct(
        private FormService $formService,
        private FieldRepository $fieldRepository,
        private FieldOptionRepository $fieldOptionRepository,
    ) {}

    public function filter(int $projectId, int $fieldId, array $filters): Collection
    {
        $field = $this->resolveField($projectId, $fieldId);

        return $this->fieldOptionRepository->filter([...$filters, 'field_id' => $field->id]);
    }

    public function find(int $projectId, int $fieldId, int $optionId): FieldOption
    {
        $field = $this->resolveField($projectId, $fieldId);

        return $this->findForField($field, $optionId);
    }

    public function create(int $projectId, int $fieldId, array $data): FieldOption
    {
        $field = $this->resolveField($projectId, $fieldId);

        return $this->fieldOptionRepository->create([
            'field_id' => $field->id,
            'label' => $data['label'],
            'value' => $data['value'],
            'sort_order' => $data['sort_order'] ?? $this->fieldOptionRepository->nextSortOrder($field->id),
        ]);
    }

    public function update(int $projectId, int $fieldId, int $optionId, array $data): FieldOption
    {
        $field = $this->resolveField($projectId, $fieldId);
        $option = $this->findForField($field, $optionId);

        return $this->fieldOptionRepository->update($option, Arr::only($data, ['label', 'value', 'sort_order']));
    }

    public function delete(int $projectId, int $fieldId, int $optionId): void
    {
        $field = $this->resolveField($projectId, $fieldId);
        $option = $this->findForField($field, $optionId);

        $this->fieldOptionRepository->delete($option);
    }

    public function sync(Field $field, array $options): void
    {
        $this->fieldOptionRepository->replaceForField($field, $options);
    }

    private function resolveField(int $projectId, int $fieldId): Field
    {
        $form = $this->formService->findOrCreate($projectId);
        $field = $this->fieldRepository->find($fieldId);

        if ($field->form_id !== $form->id) {
            throw new ModelNotFoundException;
        }

        return $field;
    }

    private function findForField(Field $field, int $optionId): FieldOption
    {
        $option = $this->fieldOptionRepository->find($optionId);

        if ($option->field_id !== $field->id) {
            throw new ModelNotFoundException;
        }

        return $option;
    }
}

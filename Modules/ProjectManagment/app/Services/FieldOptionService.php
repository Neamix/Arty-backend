<?php

namespace Modules\ProjectManagment\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Modules\ProjectManagment\Models\Field;
use Modules\ProjectManagment\Models\FieldOption;
use Modules\ProjectManagment\Repositories\FieldOptionRepository;

class FieldOptionService
{
    public function __construct(
        private FieldOptionRepository $fieldOptionRepository,
    ) {}

    public function filter(int $fieldId, array $filters): Collection
    {
        return $this->fieldOptionRepository->filter([...$filters, 'field_id' => $fieldId]);
    }

    public function find(int $optionId): FieldOption
    {
        return $this->fieldOptionRepository->find($optionId);
    }

    public function create(int $fieldId, array $data): FieldOption
    {
        return $this->fieldOptionRepository->create([
            'field_id' => $fieldId,
            'label' => $data['label'],
            'value' => $data['value'],
            'sort_order' => $data['sort_order'] ?? $this->fieldOptionRepository->nextSortOrder($fieldId),
        ]);
    }

    public function update(int $optionId, array $data): FieldOption
    {
        $option = $this->fieldOptionRepository->find($optionId);

        return $this->fieldOptionRepository->update($option, Arr::only($data, ['label', 'value', 'sort_order']));
    }

    public function delete(int $optionId): void
    {
        $option = $this->fieldOptionRepository->find($optionId);

        $this->fieldOptionRepository->delete($option);
    }

    public function sync(Field $field, array $options): void
    {
        $this->fieldOptionRepository->replaceForField($field, $options);
    }
}

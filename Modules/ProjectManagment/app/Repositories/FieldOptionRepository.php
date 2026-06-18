<?php

namespace Modules\ProjectManagment\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\ProjectManagment\Models\Field;
use Modules\ProjectManagment\Models\FieldOption;

class FieldOptionRepository
{
    public function __construct(private FieldOption $fieldOption) {}

    public function filter(array $filters): Collection
    {
        return $this->fieldOption
            ->filter($filters)
            ->orderBy('sort_order')
            ->get();
    }

    public function find(int $id): FieldOption
    {
        return $this->fieldOption->findOrFail($id);
    }

    public function create(array $data): FieldOption
    {
        return $this->fieldOption->create($data);
    }

    public function update(FieldOption $option, array $data): FieldOption
    {
        $option->update($data);

        return $option->refresh();
    }

    public function delete(FieldOption $option): void
    {
        $option->delete();
    }

    public function nextSortOrder(int $fieldId): int
    {
        return (int) $this->fieldOption
            ->where('field_id', $fieldId)
            ->max('sort_order') + 1;
    }

    public function replaceForField(Field $field, array $options): void
    {
        $field->options()->delete();

        foreach (array_values($options) as $index => $option) {
            $field->options()->create([
                'label' => $option['label'],
                'value' => $option['value'],
                'sort_order' => $index + 1,
            ]);
        }
    }
}

<?php

namespace Modules\ProjectManagment\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\ProjectManagment\Models\Field;

class FieldRepository
{
    public function __construct(private Field $field) {}

    public function filter(array $filters): Collection
    {
        return $this->field->filter($filters)
            ->orderBy('sort_order')
            ->get();
    }

    public function find(int $id): Field
    {
        return $this->field->findOrFail($id);
    }

    public function create(array $data): Field
    {
        return $this->field->create($data);
    }

    public function update(Field $field, array $data): Field
    {
        $field->update($data);

        return $field->refresh();
    }

    public function delete(Field $field): void
    {
        $field->delete();
    }

    public function nextSortOrder(int $formId): int
    {
        return (int) $this->field->where('form_id', $formId)
            ->max('sort_order') + 1;
    }

    public function titleField(int $formId): ?Field
    {
        return $this->field->where('form_id', $formId)
            ->where('is_title', true)
            ->first();
    }

    public function clearTitleFlag(int $formId, ?int $exceptFieldId = null): void
    {
        $this->field->where('form_id', $formId)
            ->where('is_title', true)
            ->when($exceptFieldId, fn ($query) => $query->where('id', '!=', $exceptFieldId))
            ->update(['is_title' => false]);
    }

    /**
     * @return array<int, int>
     */
    public function formFieldIds(int $formId): array
    {
        return $this->field->where('form_id', $formId)->pluck('id')->all();
    }
}

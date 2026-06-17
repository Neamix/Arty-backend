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
        return (int) $this->field->newQuery()
            ->where('form_id', $formId)
            ->max('sort_order') + 1;
    }
}

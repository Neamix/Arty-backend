<?php

namespace Modules\ProjectManagment\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\ProjectManagment\Models\Lead;

class LeadRepository
{
    public function __construct(private Lead $lead) {}

    public function filter(array $filters): Collection
    {
        return $this->lead->filter($filters)
            ->with('answers.field')
            ->latest()
            ->get();
    }

    public function find(int $id): Lead
    {
        return $this->lead->with('answers.field')->findOrFail($id);
    }

    public function create(array $data): Lead
    {
        return $this->lead->create($data);
    }

    public function update(Lead $lead, array $data): Lead
    {
        $lead->update($data);

        return $lead->refresh();
    }

    public function delete(Lead $lead): void
    {
        $lead->delete();
    }

    /**
     * @param  array<int, string|null>  $answers  map of field_id => value
     */
    public function syncAnswers(Lead $lead, array $answers): void
    {
        foreach ($answers as $fieldId => $value) {
            $lead->answers()->updateOrCreate(['field_id' => $fieldId], ['value' => $value]);
        }

        $lead->load('answers.field');
    }
}

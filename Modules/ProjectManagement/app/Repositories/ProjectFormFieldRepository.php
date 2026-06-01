<?php

namespace Modules\ProjectManagement\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;
use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Models\ProjectFormField;

class ProjectFormFieldRepository
{
    public function __construct(private ProjectFormField $formField) {}

    public function createForProject(Project $project, array $attributes): ProjectFormField
    {
        return $project->formFields()->create($attributes);
    }

    public function createManyForProject(Project $project, array $fields): Collection
    {
        $now = Date::now();

        $rows = [];

        foreach (array_values($fields) as $index => $field) {
            $rows[] = [
                'project_id' => $project->id,
                'label' => $field['label'],
                'type' => $field['type'],
                'is_required' => (int) $field['required'],
                'options' => isset($field['options']) ? json_encode($field['options']) : null,
                'sort_order' => $field['sort_order'] ?? ($index + 1),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->formField->newQuery()->insert($rows);

        return $project->formFields()->orderBy('id')->get();
    }
}

<?php

namespace Modules\ProjectManagement\Repositories;

use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Models\ProjectFormField;

class ProjectFormFieldRepository
{
    public function __construct(private ProjectFormField $formField) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createForProject(Project $project, array $attributes): ProjectFormField
    {
        return $project->formFields()->create($attributes);
    }

    public function existsForProject(int $projectId, int $fieldId): bool
    {
        return $this->formField->newQuery()
            ->where('project_id', $projectId)
            ->whereKey($fieldId)
            ->exists();
    }
}

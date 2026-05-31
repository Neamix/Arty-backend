<?php

namespace Modules\ProjectManagement\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\ProjectManagement\Exceptions\ProjectException;
use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Models\ProjectLead;
use Modules\ProjectManagement\Repositories\ProjectLeadRepository;
use Modules\ProjectManagement\Repositories\ProjectStageRepository;

class ProjectLeadService
{
    public function __construct(
        private ProjectLeadRepository $leadRepository,
        private ProjectStageRepository $stageRepository,
    ) {}

    /**
     * Create a lead from the project's dynamic form values.
     *
     * @param  array{values: array<int, array{field_id: int, value: mixed}>}  $data
     */
    public function create(Project $project, User $user, array $data): ProjectLead
    {
        return DB::transaction(function () use ($project, $user, $data) {
            $stage = $this->resolveDefaultStage($project);

            $lead = $this->leadRepository->createForProject($project, [
                'project_stage_id' => $stage->id,
                'created_by' => $user->id,
                'sort_order' => $this->leadRepository->nextSortOrder($stage->id),
            ]);

            $this->syncValues($lead, $data['values']);

            return $lead->load('values');
        });
    }

    /**
     * @param  array{values: array<int, array{field_id: int, value: mixed}>}  $data
     */
    public function update(ProjectLead $lead, array $data): ProjectLead
    {
        return DB::transaction(function () use ($lead, $data) {
            $this->syncValues($lead, $data['values']);

            return $lead->load('values');
        });
    }

    public function delete(ProjectLead $lead): void
    {
        $this->leadRepository->delete($lead);
    }

    /**
     * Move a lead to another stage and/or reorder it within a stage.
     */
    public function move(Project $project, ProjectLead $lead, int $stageId, int $sortOrder): ProjectLead
    {
        if (! $this->stageRepository->belongsToProject($project->id, $stageId)) {
            throw ProjectException::stageNotInProject();
        }

        return $this->leadRepository->update($lead, [
            'project_stage_id' => $stageId,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * @param  array<int, array{field_id: int, value: mixed}>  $values
     */
    private function syncValues(ProjectLead $lead, array $values): void
    {
        foreach ($values as $value) {
            $this->leadRepository->syncValue($lead, $value['field_id'], $value['value']);
        }
    }

    /**
     * Return the first stage by sort order, creating a default "Backlog" when none exist.
     */
    private function resolveDefaultStage(Project $project): \Modules\ProjectManagement\Models\ProjectStage
    {
        $stage = $this->stageRepository->firstBySortOrder($project->id);

        if ($stage === null) {
            $stage = $this->stageRepository->createForProject($project, [
                'name' => 'Backlog',
                'sort_order' => 1,
            ]);
        }

        return $stage;
    }
}

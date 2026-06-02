<?php

namespace Modules\ProjectManagement\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;
use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Models\ProjectLead;
use Modules\ProjectManagement\Models\ProjectStage;
use Modules\ProjectManagement\Repositories\ProjectLeadRepository;
use Modules\ProjectManagement\Repositories\ProjectStageRepository;

class ProjectLeadService
{
    public const SORT_GAP = 100000;

    public const SORT_MIN_GAP = 10000;

    public function __construct(
        private ProjectLeadRepository $leadRepository,
        private ProjectStageRepository $stageRepository,
    ) {}

    public function create(Project $project, User $user, array $data): ProjectLead
    {
        return DB::transaction(function () use ($project, $user, $data) {
            $stage = $this->resolveDefaultStage($project);

            $lead = $this->leadRepository->createForProject($project, [
                'project_stage_id' => $stage->id,
                'created_by' => $user->id,
                'sort_order' => $this->leadRepository->nextSortOrder($stage->id),
            ]);

            $this->leadRepository->syncValues($lead, $data['values']);

            return $lead->load('values');
        });
    }

    public function update(ProjectLead $lead, array $data): ProjectLead
    {
        return DB::transaction(function () use ($lead, $data) {
            $this->leadRepository->syncValues($lead, $data['values']);

            return $lead->load('values');
        });
    }

    public function delete(ProjectLead $lead): void
    {
        $this->leadRepository->delete($lead);
    }

    public function filter(Project $project, array $filters, int $perPage = 30): CursorPaginator
    {
        $leads = $this->leadRepository->filter($project->id, $filters, $perPage);

        foreach ($leads->items() as $lead) {
            $lead->setAttribute('card_title_field_id', $project->card_title_field_id);
        }

        return $leads;
    }

    public function leadsForStage(Project $project, ProjectStage $stage, int $perPage = 30): CursorPaginator
    {
        $leads = $this->leadRepository->paginateForStage($stage->id, $perPage);

        $leads->getCollection()->each(
            fn (ProjectLead $lead) => $lead->setAttribute('card_title_field_id', $project->card_title_field_id),
        );

        return $leads;
    }

    public function move(ProjectLead $lead, int $stageId, ?int $beforeLeadId, ?int $afterLeadId): ProjectLead
    {
        return DB::transaction(function () use ($lead, $stageId, $beforeLeadId, $afterLeadId) {
            $sortOrder = $this->resolveSortOrder($stageId, $beforeLeadId, $afterLeadId);

            return $this->leadRepository->update($lead, [
                'project_stage_id' => $stageId,
                'sort_order' => $sortOrder,
            ]);
        });
    }

    private function resolveSortOrder(int $stageId, ?int $beforeLeadId, ?int $afterLeadId): int
    {
        $before = $this->leadRepository->sortOrderOf($stageId, $beforeLeadId);
        $after = $this->leadRepository->sortOrderOf($stageId, $afterLeadId);

        if ($before === null && $after === null) {
            return self::SORT_GAP;
        }

        if ($before === null) {
            if ($after <= self::SORT_MIN_GAP) {
                $this->leadRepository->rebalanceStage($stageId);
                $after = $this->leadRepository->sortOrderOf($stageId, $afterLeadId);
            }

            return intdiv($after, 2);
        }

        if ($after === null) {
            return $before + self::SORT_GAP;
        }

        if ($after - $before <= self::SORT_MIN_GAP) {
            $this->leadRepository->rebalanceStage($stageId);
            $before = $this->leadRepository->sortOrderOf($stageId, $beforeLeadId);
            $after = $this->leadRepository->sortOrderOf($stageId, $afterLeadId);
        }

        return intdiv($before + $after, 2);
    }

    private function resolveDefaultStage(Project $project): ProjectStage
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

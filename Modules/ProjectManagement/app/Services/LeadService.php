<?php

namespace Modules\ProjectManagement\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\ActivityLog\Services\ActivityLogger;
use Modules\ProjectManagement\Models\Lead;
use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Models\Stage;
use Modules\ProjectManagement\Repositories\LeadRepository;
use Modules\ProjectManagement\Repositories\ProjectRepository;
use Modules\ProjectManagement\Repositories\StageRepository;

class LeadService
{
    public const SORT_GAP = 100000;

    public const SORT_MIN_GAP = 10000;

    public function __construct(
        private LeadRepository $leadRepository,
        private StageRepository $stageRepository,
        private ProjectRepository $projectRepository,
        private ActivityLogger $activityLogger,
    ) {}

    public function create(Project $project, User $user, array $data): Lead
    {
        return DB::transaction(function () use ($project, $user, $data) {
            $stage = $this->resolveDefaultStage($project);

            $lead = $this->leadRepository->createForProject($project, [
                'stage_id' => $stage->id,
                'created_by' => $user->id,
                'sort_order' => $this->leadRepository->nextSortOrder($stage->id),
            ]);

            $this->leadRepository->syncValues($lead, $data['values']);
            $this->projectRepository->incrementLeadCount($project);

            $this->activityLogger->log(
                event: 'created',
                subject: $lead,
                description: Auth::user()->name . 'Created the lead',
                causer: $user,
                properties: ['stage_id' => $stage->id],
                workspaceId: $project->workspace_id,
            );

            return $lead->load('values');
        });
    }

    public function update(Lead $lead, Project $project, User $causer, array $data): Lead
    {
        return DB::transaction(function () use ($lead, $project, $causer, $data) {
            $this->leadRepository->syncValues($lead, $data['values']);

            $this->activityLogger->log(
                event: 'updated',
                subject: $lead,
                causer: $causer,
                properties: ['values' => $data['values']],
                workspaceId: $project->workspace_id,
            );

            return $lead->load('values');
        });
    }

    public function delete(Lead $lead, Project $project, User $causer): void
    {
        DB::transaction(function () use ($lead, $project, $causer) {
            $this->activityLogger->log(
                event: 'deleted',
                subject: $lead,
                causer: $causer,
                workspaceId: $project->workspace_id,
            );

            $this->leadRepository->delete($lead);
            $this->projectRepository->decrementLeadCount($project);
        });
    }

    public function filter(Project $project, array $filters, int $perPage = 30): CursorPaginator
    {
        $leads = $this->leadRepository->filter($project->id, $filters, $perPage);

        foreach ($leads->items() as $lead) {
            $lead->setAttribute('card_title_field_id', $project->card_title_field_id);
        }

        return $leads;
    }

    public function leadsForStage(Project $project, Stage $stage, int $perPage = 30): CursorPaginator
    {
        $leads = $this->leadRepository->paginateForStage($stage->id, $perPage);

        $leads->getCollection()->each(
            fn (Lead $lead) => $lead->setAttribute('card_title_field_id', $project->card_title_field_id),
        );

        return $leads;
    }

    public function move(Lead $lead, Project $project, User $causer, int $stageId, ?int $beforeLeadId, ?int $afterLeadId): Lead
    {
        return DB::transaction(function () use ($lead, $project, $causer, $stageId, $beforeLeadId, $afterLeadId) {
            $fromStageId = $lead->stage_id;
            $sortOrder = $this->resolveSortOrder($stageId, $beforeLeadId, $afterLeadId);

            $lead = $this->leadRepository->update($lead, [
                'stage_id' => $stageId,
                'sort_order' => $sortOrder,
            ]);

            $this->activityLogger->log(
                event: 'moved',
                subject: $lead,
                causer: $causer,
                properties: ['from_stage_id' => $fromStageId, 'to_stage_id' => $stageId],
                workspaceId: $project->workspace_id,
            );

            return $lead;
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

    private function resolveDefaultStage(Project $project): Stage
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

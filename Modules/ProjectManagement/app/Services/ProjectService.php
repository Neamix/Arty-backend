<?php

namespace Modules\ProjectManagement\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\ProjectManagement\Models\Project;
use Modules\ProjectManagement\Repositories\ProjectFormFieldRepository;
use Modules\ProjectManagement\Repositories\ProjectRepository;
use Modules\ProjectManagement\Repositories\ProjectStageRepository;

class ProjectService
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private ProjectFormFieldRepository $formFieldRepository,
        private ProjectStageRepository $stageRepository,
    ) {}

    public function listForUser(User $user): Collection
    {
        return $this->projectRepository->forUser($user->id);
    }

    public function create(User $user, array $data): Project
    {
        return DB::transaction(function () use ($user, $data) {
            $project = $this->projectRepository->create([
                'name' => $data['name'],
                'icon' => $data['icon'] ?? null,
                'created_by' => $user->id,
            ]);

            $fields = $this->formFieldRepository->createManyForProject($project, $data['form_fields']);

            $titleField = $fields[$data['card_title_field_index']];

            $this->projectRepository->update($project, [
                'card_title_field_id' => $titleField->id,
            ]);

            $this->createStages($project, $data['stages'] ?? []);

            return $this->projectRepository->loadDetails($project->refresh());
        });
    }

    public function update(Project $project, array $data): Project
    {
        $this->projectRepository->update($project, array_filter(
            $data,
            fn (string $key): bool => in_array($key, ['name', 'icon', 'card_title_field_id'], true),
            ARRAY_FILTER_USE_KEY,
        ));

        return $this->projectRepository->loadDetails($project->refresh());
    }

    public function delete(Project $project): void
    {
        $this->projectRepository->delete($project);
    }

    public function board(Project $project): Project
    {
        $project = $this->projectRepository->loadBoard($project);

        $project->stages->each(function ($stage) use ($project): void {
            $stage->leads->each(function ($lead) use ($project): void {
                $lead->setAttribute('card_title_field_id', $project->card_title_field_id);
            });
        });

        return $project;
    }

    private function createStages(Project $project, array $stages): void
    {
        if ($stages === []) {
            $this->stageRepository->createForProject($project, [
                'name' => 'Backlog',
                'sort_order' => 1,
            ]);

            return;
        }

        $this->stageRepository->createManyForProject($project, $stages);
    }
}

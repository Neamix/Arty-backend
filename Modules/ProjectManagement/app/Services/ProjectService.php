<?php

namespace Modules\ProjectManagement\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\ProjectManagement\Exceptions\ProjectException;
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

            $createdFields = [];

            // A very bad behaviour to loop on query u can use insert many
            foreach (array_values($data['form_fields']) as $index => $field) {
                $createdFields[$index] = $this->formFieldRepository->createForProject($project, [
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'is_required' => $field['required'],
                    'options' => $field['options'] ?? null,
                    'sort_order' => $field['sort_order'] ?? ($index + 1),
                ]);
            }

            $titleField = $createdFields[$data['card_title_field_index']] ?? null;

            // dont ever throw exception inside the servie this is form request job
            if ($titleField === null) {
                throw ProjectException::invalidCardTitleField();
            }

            $this->projectRepository->update($project, [
                'card_title_field_id' => $titleField->id,
            ]);

            $this->createStages($project, $data['stages'] ?? []);

            return $this->projectRepository->loadDetails($project->refresh());
        });
    }

    public function update(Project $project, array $data): Project
    {
        if (array_key_exists('card_title_field_id', $data)) {
            $valid = $this->formFieldRepository->existsForProject($project->id, $data['card_title_field_id']);

            if (! $valid) {
                throw ProjectException::invalidCardTitleField();
            }
        }

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
        // All of this is a bad approch you are load a leads and stages and values in same time this is a huge bottlenick load stages and board main information then make another endpoint that load only 30 lead for each stage then on user scroll down he can load more leads for the scrolled board
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

        // Dont make for loop inside it query
        foreach (array_values($stages) as $index => $stage) {
            $this->stageRepository->createForProject($project, [
                'name' => $stage['name'],
                'sort_order' => $stage['sort_order'] ?? ($index + 1),
            ]);
        }
    }
}

<?php

namespace Modules\ProjectManagment\Services;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\ProjectManagment\Models\Field;
use Modules\ProjectManagment\Models\Lead;
use Modules\ProjectManagment\Models\Stage;
use Modules\ProjectManagment\Repositories\FieldRepository;
use Modules\ProjectManagment\Repositories\FormRepository;
use Modules\ProjectManagment\Repositories\LeadRepository;
use Modules\ProjectManagment\Repositories\ProjectRepository;
use Modules\ProjectManagment\Repositories\StageRepository;

class LeadService
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private StageRepository $stageRepository,
        private FormRepository $formRepository,
        private FieldRepository $fieldRepository,
        private LeadRepository $leadRepository,
    ) {}

    public function filter(array $data): CursorPaginator
    {
        $stage = $this->resolveStage($data);

        return $this->leadRepository->filter([
            ...Arr::only($data, ['due_from', 'due_to']),
            'stage_id' => $stage->id,
        ]);
    }

    public function find(array $data): Lead
    {
        $stage = $this->resolveStage($data);

        return $this->findForStage($stage, $data['lead_id']);
    }

    public function create(array $data): Lead
    {
        $stage = $this->resolveStage($data);
        $titleField = $this->requireTitleField($data['project_id']);

        return DB::transaction(function () use ($stage, $titleField, $data) {
            $lead = $this->leadRepository->create([
                'stage_id' => $stage->id,
                'due_date' => $data['due_date'] ?? null,
            ]);

            $this->leadRepository->syncAnswers($lead, $this->buildAnswers($data['project_id'], $titleField, $data['answers'] ?? []));

            return $lead;
        });
    }

    public function update(array $data): Lead
    {
        $stage = $this->resolveStage($data);
        $lead = $this->findForStage($stage, $data['lead_id']);
        $titleField = $this->requireTitleField($data['project_id']);

        return DB::transaction(function () use ($lead, $data, $titleField) {
            if (array_key_exists('due_date', $data)) {
                $lead = $this->leadRepository->update($lead, ['due_date' => $data['due_date']]);
            }

            if (array_key_exists('answers', $data)) {
                $this->leadRepository->syncAnswers($lead, $this->buildAnswers($data['project_id'], $titleField, $data['answers'] ?? []));
            }

            return $lead;
        });
    }

    public function delete(array $data): void
    {
        $stage = $this->resolveStage($data);
        $lead = $this->findForStage($stage, $data['lead_id']);

        $this->leadRepository->delete($lead);
    }

    private function resolveStage(array $data): Stage
    {
        $project = $this->projectRepository->find($data['project_id']);
        $stage = $this->stageRepository->find($data['stage_id']);

        if ($stage->project_id !== $project->id) {
            throw new ModelNotFoundException;
        }

        return $stage;
    }

    private function findForStage(Stage $stage, int $leadId): Lead
    {
        $lead = $this->leadRepository->find($leadId);

        if ($lead->stage_id !== $stage->id) {
            throw new ModelNotFoundException;
        }

        return $lead;
    }

    private function requireTitleField(int $projectId): Field
    {
        $form = $this->formRepository->firstOrCreateForProject($projectId);
        $titleField = $this->fieldRepository->titleField($form->id);

        if ($titleField === null || $titleField->default_value === null || $titleField->default_value === '') {
            throw ValidationException::withMessages([
                'form' => 'The project form must have a title field with a default value before creating leads.',
            ]);
        }

        return $titleField;
    }

    /**
     * @param  array<int, array{field_id: int, value: string|null}>  $answers
     * @return array<int, string|null> map of field_id => value, always including the title field
     */
    private function buildAnswers(int $projectId, Field $titleField, array $answers): array
    {
        $form = $this->formRepository->firstOrCreateForProject($projectId);
        $allowedIds = $this->fieldRepository->formFieldIds($form->id);

        $map = [];

        foreach ($answers as $answer) {
            if (! in_array($answer['field_id'], $allowedIds, true)) {
                throw ValidationException::withMessages([
                    'answers' => 'Each answer must reference a field that belongs to the project form.',
                ]);
            }

            $map[$answer['field_id']] = $answer['value'] ?? null;
        }

        if (! array_key_exists($titleField->id, $map)) {
            $map[$titleField->id] = null;
        }

        return $map;
    }
}

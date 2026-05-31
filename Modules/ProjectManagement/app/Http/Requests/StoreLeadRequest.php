<?php

namespace Modules\ProjectManagement\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Modules\ProjectManagement\Models\Project;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'values' => ['required', 'array', 'min:1'],
            'values.*.field_id' => ['required', 'integer'],
            'values.*.value' => ['nullable'],
        ];
    }

    /**
     * Validate that submitted field ids belong to the project and that required
     * fields are present, mirroring the dynamic form definition.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Project|null $project */
            $project = $this->route('project');

            if (! $project instanceof Project) {
                return;
            }

            $fields = $project->formFields()->get();
            $allowedIds = $fields->pluck('id')->all();
            $submitted = collect($this->input('values', []));

            foreach ($submitted as $i => $value) {
                if (! in_array((int) ($value['field_id'] ?? 0), $allowedIds, true)) {
                    $validator->errors()->add("values.{$i}.field_id", 'This field does not belong to the project.');
                }
            }

            $submittedIds = $submitted->pluck('field_id')->map(fn ($id): int => (int) $id)->all();

            foreach ($fields->where('is_required', true) as $field) {
                if (! in_array($field->id, $submittedIds, true)) {
                    $validator->errors()->add('values', "The field \"{$field->label}\" is required.");
                }
            }
        });
    }
}

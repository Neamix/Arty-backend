<?php

namespace Modules\ProjectManagment\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'project_id' => $this->route('project'),
            'stage_id' => $this->route('stage'),
            'lead_id' => $this->route('lead'),
        ]);
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer'],
            'stage_id' => ['required', 'integer'],
            'lead_id' => ['required', 'integer'],
            'due_date' => ['nullable', 'date'],
            'answers' => ['nullable', 'array'],
            'answers.*.field_id' => ['required_with:answers', 'integer', 'exists:fields,id'],
            'answers.*.value' => ['nullable', 'string'],
        ];
    }
}

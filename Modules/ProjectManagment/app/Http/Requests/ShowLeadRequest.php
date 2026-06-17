<?php

namespace Modules\ProjectManagment\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ShowLeadRequest extends FormRequest
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
        ];
    }
}

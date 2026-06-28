<?php

namespace Modules\ProjectManagment\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ShowProjectModeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'project_id' => $this->route('project'),
        ]);
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer'],
            'week_start' => ['nullable', 'date'],
        ];
    }
}

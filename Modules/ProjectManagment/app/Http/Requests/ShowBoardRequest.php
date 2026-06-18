<?php

namespace Modules\ProjectManagment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowBoardRequest extends FormRequest
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

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:stages,project_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.exists' => 'This project has no stages to display on the board.',
        ];
    }
}

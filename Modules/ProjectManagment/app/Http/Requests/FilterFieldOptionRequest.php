<?php

namespace Modules\ProjectManagment\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FilterFieldOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:255'],
            'value' => ['nullable', 'string', 'max:255'],
        ];
    }
}

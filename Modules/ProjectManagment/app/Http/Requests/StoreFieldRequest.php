<?php

namespace Modules\ProjectManagment\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\ProjectManagment\Enums\FieldType;

class StoreFieldRequest extends FormRequest
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
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(FieldType::class)],
            'is_required' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'config' => ['nullable', 'array'],
            'options' => ['required_if:type,'.FieldType::Select->value, 'array', 'min:1'],
            'options.*.label' => ['required_with:options', 'string', 'max:255'],
            'options.*.value' => ['required_with:options', 'string', 'max:255'],
        ];
    }
}

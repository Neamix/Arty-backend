<?php

namespace Modules\ProjectManagement\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\ProjectManagement\Enums\FieldType;

class StoreProjectRequest extends FormRequest
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
        $optionable = implode(',', FieldType::optionableValues());

        return [
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],

            'form_fields' => ['required', 'array', 'min:1'],
            'form_fields.*.label' => ['required', 'string', 'max:255'],
            'form_fields.*.type' => ['required', 'string', Rule::in(FieldType::values())],
            'form_fields.*.required' => ['required', 'boolean'],
            'form_fields.*.sort_order' => ['nullable', 'integer', 'min:1'],
            'form_fields.*.options' => ["required_if:form_fields.*.type,{$optionable}", 'nullable', 'array', 'min:1'],
            'form_fields.*.options.*' => ['required', 'string', 'max:255'],

            'card_title_field_index' => ['required', 'integer', 'min:0'],

            'stages' => ['nullable', 'array'],
            'stages.*.name' => ['required_with:stages', 'string', 'max:255'],
            'stages.*.sort_order' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $fields = $this->input('form_fields', []);
            $index = $this->input('card_title_field_index');

            if (is_array($fields) && is_int($index) && ! array_key_exists($index, array_values($fields))) {
                $validator->errors()->add(
                    'card_title_field_index',
                    'The selected card title field is invalid.',
                );
            }
        });
    }
}

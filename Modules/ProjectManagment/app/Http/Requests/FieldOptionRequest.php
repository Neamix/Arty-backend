<?php

namespace Modules\ProjectManagment\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FieldOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'field_id' => $this->route('field'),
            'option_id' => $this->route('option'),
        ]);
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return $this->ownershipRules();
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    protected function ownershipRules(): array
    {
        $rules = [
            'field_id' => [
                'required',
                Rule::exists('fields', 'id')->where(function (Builder $query): void {
                    $query->whereIn('form_id', function (Builder $sub): void {
                        $sub->select('id')->from('forms')->where('project_id', $this->route('project'));
                    });
                }),
            ],
        ];

        if ($this->route('option') !== null) {
            $rules['option_id'] = [
                'required',
                Rule::exists('field_options', 'id')->where('field_id', $this->route('field')),
            ];
        }

        return $rules;
    }
}

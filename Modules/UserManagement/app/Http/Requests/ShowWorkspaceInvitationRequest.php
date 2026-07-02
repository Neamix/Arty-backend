<?php

namespace Modules\UserManagement\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\UserManagement\Repositories\WorkspaceInvitationRepository;

class ShowWorkspaceInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->route('token'))) {
            $this->merge(['token' => $this->route('token')]);
        }
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(WorkspaceInvitationRepository $workspaceInvitationRepository): array
    {
        return [
            function (Validator $validator) use ($workspaceInvitationRepository): void {
                if ($validator->errors()->has('token')) {
                    return;
                }

                try {
                    $invitation = $workspaceInvitationRepository->findByPlainToken((string) $this->input('token'));
                } catch (ModelNotFoundException) {
                    return;
                }

                if ($invitation->accepted_at !== null) {
                    $validator->errors()->add('token', 'This invitation has already been accepted.');
                } elseif ($invitation->expires_at->isPast()) {
                    $validator->errors()->add('token', 'This invitation has expired.');
                }
            },
        ];
    }
}

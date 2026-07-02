<?php

namespace Modules\UserManagement\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\UserManagement\Repositories\WorkspaceInvitationRepository;

class InviteWorkspaceMemberRequest extends WorkspaceOwnerRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => Str::lower($this->input('email'))]);
        }
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where('workspace_id', $this->workspaceId()),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'This email already belongs to an existing user.',
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(WorkspaceInvitationRepository $workspaceInvitationRepository): array
    {
        return [
            function (Validator $validator) use ($workspaceInvitationRepository): void {
                if ($validator->errors()->has('email')) {
                    return;
                }

                if ($workspaceInvitationRepository->pendingExists((int) $this->workspaceId(), (string) $this->input('email'))) {
                    $validator->errors()->add('email', 'This email already has a pending invitation.');
                }
            },
        ];
    }
}

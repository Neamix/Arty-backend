<?php

namespace Modules\UserManagement\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;
use Modules\UserManagement\Repositories\UserRepository;
use Modules\UserManagement\Repositories\WorkspaceInvitationRepository;

class RegisterWorkspaceInvitationRequest extends ShowWorkspaceInvitationRequest
{
    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(
        WorkspaceInvitationRepository $workspaceInvitationRepository,
        ?UserRepository $userRepository = null,
    ): array {
        $userRepository ??= $this->container->make(UserRepository::class);

        return [
            ...parent::after($workspaceInvitationRepository),
            function (Validator $validator) use ($workspaceInvitationRepository, $userRepository): void {
                if ($validator->errors()->has('token')) {
                    return;
                }

                try {
                    $invitation = $workspaceInvitationRepository->findByPlainToken((string) $this->input('token'));
                } catch (ModelNotFoundException) {
                    return;
                }

                if ($userRepository->findByEmail($invitation->email)) {
                    $validator->errors()->add('token', 'This invitation email already belongs to an existing user.');
                }
            },
        ];
    }
}

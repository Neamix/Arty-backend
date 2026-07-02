<?php

namespace Modules\UserManagement\Http\Requests;

use Illuminate\Validation\Validator;
use Modules\UserManagement\Repositories\WorkspaceMemberRepository;

class DeleteRoleRequest extends WorkspaceOwnerRequest
{
    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(WorkspaceMemberRepository $workspaceMemberRepository): array
    {
        return [
            function (Validator $validator) use ($workspaceMemberRepository): void {
                if ($workspaceMemberRepository->existsForRole((int) $this->route('role'))) {
                    $validator->errors()->add('role', 'Role is assigned to one or more workspace members.');
                }
            },
        ];
    }
}

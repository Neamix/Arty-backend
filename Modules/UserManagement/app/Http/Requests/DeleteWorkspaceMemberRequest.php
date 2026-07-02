<?php

namespace Modules\UserManagement\Http\Requests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Validator;
use Modules\UserManagement\Repositories\WorkspaceMemberRepository;

class DeleteWorkspaceMemberRequest extends WorkspaceOwnerRequest
{
    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(WorkspaceMemberRepository $workspaceMemberRepository): array
    {
        return [
            function (Validator $validator) use ($workspaceMemberRepository): void {
                try {
                    $member = $workspaceMemberRepository->findByUser((int) $this->route('user'));
                } catch (ModelNotFoundException) {
                    return;
                }

                if ($member->is_owner && $workspaceMemberRepository->ownerCount($member->workspace_id) <= 1) {
                    $validator->errors()->add('user', 'The last workspace owner cannot be removed.');
                }
            },
        ];
    }
}

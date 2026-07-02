<?php

namespace Modules\UserManagement\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateWorkspaceMemberRoleRequest extends WorkspaceOwnerRequest
{
    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where('workspace_id', $this->workspaceId()),
            ],
        ];
    }
}

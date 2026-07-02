<?php

namespace Modules\UserManagement\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Modules\UserManagement\Support\PermissionRegistry;

class UpdateRoleRequest extends WorkspaceOwnerRequest
{
    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'api')
                    ->where('workspace_id', $this->workspaceId())
                    ->ignore($this->route('role')),
            ],
            'permissions' => ['present', 'array'],
            'permissions.*' => ['required', 'string', Rule::in(PermissionRegistry::all())],
        ];
    }
}

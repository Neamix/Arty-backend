<?php

namespace Modules\UserManagement\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\UserManagement\Models\WorkspaceMember;

/**
 * @mixin WorkspaceMember
 */
class WorkspaceMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'role' => new RoleResource($this->whenLoaded('role')),
            'is_owner' => $this->is_owner,
            'created_at' => $this->created_at,
        ];
    }
}

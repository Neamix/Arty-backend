<?php

namespace Modules\UserManagement\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\UserManagement\Models\WorkspaceInvitation;

/**
 * @mixin WorkspaceInvitation
 */
class WorkspaceInvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'workspace' => new WorkspaceResource($this->whenLoaded('workspace')),
            'role' => new RoleResource($this->whenLoaded('role')),
            'expires_at' => $this->expires_at,
            'accepted_at' => $this->accepted_at,
        ];
    }
}

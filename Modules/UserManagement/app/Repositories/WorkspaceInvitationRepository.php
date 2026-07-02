<?php

namespace Modules\UserManagement\Repositories;

use Modules\UserManagement\Models\WorkspaceInvitation;

class WorkspaceInvitationRepository
{
    public function __construct(private WorkspaceInvitation $workspaceInvitation) {}

    public function create(array $attributes): WorkspaceInvitation
    {
        return $this->workspaceInvitation->create($attributes);
    }

    public function findByPlainToken(string $token): WorkspaceInvitation
    {
        return $this->workspaceInvitation
            ->withoutGlobalScopes()
            ->with(['workspace', 'role'])
            ->filter(['token' => hash('sha256', $token)])
            ->firstOrFail();
    }

    public function pendingExists(int $workspaceId, string $email): bool
    {
        return $this->workspaceInvitation
            ->withoutGlobalScopes()
            ->filter([
                'workspace_id' => $workspaceId,
                'email_exact' => $email,
                'accepted' => false,
            ])
            ->exists();
    }

    public function markAccepted(WorkspaceInvitation $workspaceInvitation): WorkspaceInvitation
    {
        $workspaceInvitation->forceFill([
            'accepted_at' => now(),
        ])->save();

        return $workspaceInvitation->refresh()->load(['workspace', 'role']);
    }
}

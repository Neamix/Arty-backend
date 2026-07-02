<?php

namespace Modules\UserManagement\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\UserManagement\Models\WorkspaceMember;

class WorkspaceMemberRepository
{
    public function __construct(private WorkspaceMember $workspaceMember) {}

    public function filter(array $filters): Collection
    {
        return $this->workspaceMember
            ->with(['user', 'role'])
            ->filter($filters)
            ->latest()
            ->get();
    }

    public function find(int $id): WorkspaceMember
    {
        return $this->workspaceMember->findOrFail($id);
    }

    public function findByUser(int $userId): WorkspaceMember
    {
        return $this->workspaceMember->filter(['user_id' => $userId])->firstOrFail();
    }

    public function create(array $attributes): WorkspaceMember
    {
        return $this->workspaceMember->create($attributes);
    }

    public function update(WorkspaceMember $workspaceMember, array $data): WorkspaceMember
    {
        $workspaceMember->update($data);

        return $workspaceMember->refresh()->load(['user', 'role']);
    }

    public function delete(WorkspaceMember $workspaceMember): void
    {
        $workspaceMember->delete();
    }

    public function ownerCount(int $workspaceId): int
    {
        return $this->workspaceMember
            ->withoutGlobalScopes()
            ->filter([
                'workspace_id' => $workspaceId,
                'is_owner' => true,
            ])
            ->count();
    }

    public function existsForUser(int $workspaceId, int $userId): bool
    {
        return $this->workspaceMember
            ->withoutGlobalScopes()
            ->filter([
                'workspace_id' => $workspaceId,
                'user_id' => $userId,
            ])
            ->exists();
    }

    public function existsForRole(int $roleId): bool
    {
        return $this->workspaceMember
            ->filter(['role_id' => $roleId])
            ->exists();
    }

    public function isOwner(int $workspaceId, int $userId): bool
    {
        return $this->workspaceMember
            ->withoutGlobalScopes()
            ->filter([
                'workspace_id' => $workspaceId,
                'user_id' => $userId,
                'is_owner' => true,
            ])
            ->exists();
    }
}

<?php

namespace Modules\UserManagement\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\UserManagement\Models\WorkspaceMember;
use Modules\UserManagement\Repositories\WorkspaceMemberRepository;

class WorkspaceMemberService
{
    public function __construct(private WorkspaceMemberRepository $workspaceMemberRepository) {}

    public function filter(array $filters): Collection
    {
        return $this->workspaceMemberRepository->filter($filters);
    }

    public function updateRole(int $memberUserId, array $data): WorkspaceMember
    {
        $member = $this->workspaceMemberRepository->findByUser($memberUserId);

        return $this->workspaceMemberRepository->update($member, [
            'role_id' => $data['role_id'],
        ]);
    }

    public function delete(int $memberUserId): void
    {
        $this->workspaceMemberRepository->delete($this->workspaceMemberRepository->findByUser($memberUserId));
    }
}

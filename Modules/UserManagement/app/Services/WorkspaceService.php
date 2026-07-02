<?php

namespace Modules\UserManagement\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Modules\UserManagement\Models\Workspace;
use Modules\UserManagement\Repositories\WorkspaceMemberRepository;
use Modules\UserManagement\Repositories\WorkspaceRepository;

class WorkspaceService
{
    public function __construct(
        private WorkspaceRepository $workspaceRepository,
        private WorkspaceMemberRepository $workspaceMemberRepository,
    ) {}

    public function createForOwner(User $owner, ?string $name = null): Workspace
    {
        $name = $name ?: $owner->name."'s Workspace";

        $workspace = $this->workspaceRepository->create([
            'owner_id' => $owner->id,
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
        ]);

        $this->workspaceMemberRepository->create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'role_id' => null,
            'is_owner' => true,
        ]);

        return $workspace;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'workspace';
        $slug = $base;

        while ($this->workspaceRepository->slugExists($slug)) {
            $slug = $base.'-'.Str::lower(Str::random(6));
        }

        return $slug;
    }
}

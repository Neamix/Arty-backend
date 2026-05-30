<?php

namespace Modules\UserManagement\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\UserManagement\Models\Workspace;

class WorkspaceRepository
{
    public function __construct(private Workspace $workspace) {}

    public function create(array $attributes): Workspace
    {
        return $this->workspace->newQuery()->create($attributes);
    }

    public function forOwner(int $ownerId): Collection
    {
        return $this->workspace->newQuery()->where('owner_id', $ownerId)->get();
    }

    public function slugExists(string $slug): bool
    {
        return $this->workspace->newQuery()->where('slug', $slug)->exists();
    }
}

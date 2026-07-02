<?php

namespace Modules\UserManagement\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\UserManagement\Models\Workspace;

class WorkspaceRepository
{
    public function __construct(private Workspace $workspace) {}

    public function create(array $attributes): Workspace
    {
        return $this->workspace->create($attributes);
    }

    public function forOwner(int $ownerId): Collection
    {
        return $this->workspace->where('owner_id', $ownerId)->get();
    }

    public function find(int $id): Workspace
    {
        return $this->workspace->findOrFail($id);
    }

    public function slugExists(string $slug): bool
    {
        return $this->workspace->where('slug', $slug)->exists();
    }
}

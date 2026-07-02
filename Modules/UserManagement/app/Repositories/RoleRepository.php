<?php

namespace Modules\UserManagement\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\UserManagement\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleRepository
{
    public function __construct(
        private Role $role,
        private Permission $permission,
    ) {}

    public function filter(array $filters): Collection
    {
        return $this->role
            ->with('permissions')
            ->filter($filters)
            ->latest()
            ->get();
    }

    public function find(int $id): Role
    {
        return $this->role->with('permissions')->findOrFail($id);
    }

    public function create(array $attributes): Role
    {
        return $this->role->create($attributes);
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($data);

        return $role;
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }

    /**
     * @param  array<int, string>  $names
     */
    public function ensurePermissionsExist(array $names): void
    {
        $existing = $this->permission
            ->whereIn('name', $names)
            ->where('guard_name', 'api')
            ->pluck('name')
            ->all();

        foreach (array_diff($names, $existing) as $name) {
            $this->permission->create(['name' => $name, 'guard_name' => 'api']);
        }
    }
}

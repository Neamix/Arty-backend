<?php

namespace Modules\UserManagement\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\UserManagement\Models\Role;
use Modules\UserManagement\Repositories\RoleRepository;
use Modules\UserManagement\Support\PermissionRegistry;

class RoleService
{
    public function __construct(private RoleRepository $roleRepository) {}

    public function filter(array $filters): Collection
    {
        return $this->roleRepository->filter($filters);
    }

    public function find(int $id): Role
    {
        return $this->roleRepository->find($id);
    }

    public function create(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $this->roleRepository->ensurePermissionsExist($data['permissions'] ?? []);

            $role = $this->roleRepository->create([
                'name' => $data['name'],
                'guard_name' => 'api',
            ]);

            $role->syncPermissions($data['permissions'] ?? []);

            return $role->refresh()->load('permissions');
        });
    }

    public function update(int $id, array $data): Role
    {
        return DB::transaction(function () use ($id, $data) {
            $role = $this->roleRepository->find($id);

            $this->roleRepository->ensurePermissionsExist($data['permissions'] ?? []);

            $updated = $this->roleRepository->update($role, [
                'name' => $data['name'],
            ]);

            $updated->syncPermissions($data['permissions'] ?? []);

            return $updated->refresh()->load('permissions');
        });
    }

    public function delete(int $id): void
    {
        $this->roleRepository->delete($this->roleRepository->find($id));
    }

    public function permissions(): array
    {
        $this->roleRepository->ensurePermissionsExist(PermissionRegistry::all());

        return PermissionRegistry::grouped();
    }
}

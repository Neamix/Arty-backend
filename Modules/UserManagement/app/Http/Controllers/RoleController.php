<?php

namespace Modules\UserManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\UserManagement\Http\Requests\DeleteRoleRequest;
use Modules\UserManagement\Http\Requests\FilterRoleRequest;
use Modules\UserManagement\Http\Requests\StoreRoleRequest;
use Modules\UserManagement\Http\Requests\UpdateRoleRequest;
use Modules\UserManagement\Http\Requests\WorkspaceOwnerRequest;
use Modules\UserManagement\Http\Resources\RoleResource;
use Modules\UserManagement\Services\RoleService;

class RoleController extends Controller
{
    public function __construct(private RoleService $roleService) {}

    public function index(FilterRoleRequest $request): JsonResponse
    {
        return response()->json([
            'data' => RoleResource::collection($this->roleService->filter($request->validated())),
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->create($request->validated());

        return response()->json([
            'message' => 'Role created successfully.',
            'data' => new RoleResource($role),
        ], 201);
    }

    public function show(WorkspaceOwnerRequest $request, int $role): JsonResponse
    {
        return response()->json([
            'data' => new RoleResource($this->roleService->find($role)),
        ]);
    }

    public function update(UpdateRoleRequest $request, int $role): JsonResponse
    {
        $updated = $this->roleService->update($role, $request->validated());

        return response()->json([
            'message' => 'Role updated successfully.',
            'data' => new RoleResource($updated),
        ]);
    }

    public function destroy(DeleteRoleRequest $request, int $role): JsonResponse
    {
        $this->roleService->delete($role);

        return response()->json([
            'message' => 'Role deleted successfully.',
        ]);
    }
}

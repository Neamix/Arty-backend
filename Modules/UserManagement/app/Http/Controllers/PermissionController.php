<?php

namespace Modules\UserManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\UserManagement\Http\Requests\WorkspaceOwnerRequest;
use Modules\UserManagement\Services\RoleService;

class PermissionController extends Controller
{
    public function __construct(private RoleService $roleService) {}

    public function index(WorkspaceOwnerRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->roleService->permissions(),
        ]);
    }
}

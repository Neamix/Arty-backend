<?php

namespace Modules\UserManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\UserManagement\Http\Requests\RegisterWorkspaceInvitationRequest;
use Modules\UserManagement\Http\Requests\ShowWorkspaceInvitationRequest;
use Modules\UserManagement\Http\Resources\UserResource;
use Modules\UserManagement\Http\Resources\WorkspaceInvitationResource;
use Modules\UserManagement\Services\WorkspaceInvitationService;

class WorkspaceInvitationController extends Controller
{
    public function __construct(private WorkspaceInvitationService $workspaceInvitationService) {}

    public function show(ShowWorkspaceInvitationRequest $request, string $token): JsonResponse
    {
        return response()->json([
            'data' => new WorkspaceInvitationResource($this->workspaceInvitationService->findByToken($token)),
        ]);
    }

    public function register(RegisterWorkspaceInvitationRequest $request): JsonResponse
    {
        $result = $this->workspaceInvitationService->register($request->validated());

        return response()->json([
            'message' => 'Invitation accepted successfully.',
            'user' => new UserResource($result['user']),
            'invitation' => new WorkspaceInvitationResource($result['invitation']),
            'token' => $result['token'],
        ], 201);
    }
}

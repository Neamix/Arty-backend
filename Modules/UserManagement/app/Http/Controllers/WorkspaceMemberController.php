<?php

namespace Modules\UserManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\UserManagement\Http\Requests\DeleteWorkspaceMemberRequest;
use Modules\UserManagement\Http\Requests\FilterWorkspaceMemberRequest;
use Modules\UserManagement\Http\Requests\InviteWorkspaceMemberRequest;
use Modules\UserManagement\Http\Requests\UpdateWorkspaceMemberRoleRequest;
use Modules\UserManagement\Http\Resources\WorkspaceInvitationResource;
use Modules\UserManagement\Http\Resources\WorkspaceMemberResource;
use Modules\UserManagement\Services\WorkspaceInvitationService;
use Modules\UserManagement\Services\WorkspaceMemberService;

class WorkspaceMemberController extends Controller
{
    public function __construct(
        private WorkspaceMemberService $workspaceMemberService,
        private WorkspaceInvitationService $workspaceInvitationService,
    ) {}

    public function index(FilterWorkspaceMemberRequest $request): JsonResponse
    {
        return response()->json([
            'data' => WorkspaceMemberResource::collection(
                $this->workspaceMemberService->filter($request->validated())
            ),
        ]);
    }

    public function invite(InviteWorkspaceMemberRequest $request): JsonResponse
    {
        $invitation = $this->workspaceInvitationService->invite($request->user(), $request->validated());

        return response()->json([
            'message' => 'Workspace invitation sent successfully.',
            'data' => new WorkspaceInvitationResource($invitation),
        ], 201);
    }

    public function updateRole(UpdateWorkspaceMemberRoleRequest $request, int $user): JsonResponse
    {
        $member = $this->workspaceMemberService->updateRole($user, $request->validated());

        return response()->json([
            'message' => 'Workspace member role updated successfully.',
            'data' => new WorkspaceMemberResource($member),
        ]);
    }

    public function destroy(DeleteWorkspaceMemberRequest $request, int $user): JsonResponse
    {
        $this->workspaceMemberService->delete($user);

        return response()->json([
            'message' => 'Workspace member removed successfully.',
        ]);
    }
}

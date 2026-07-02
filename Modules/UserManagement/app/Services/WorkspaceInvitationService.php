<?php

namespace Modules\UserManagement\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\UserManagement\Events\WorkspaceInvitationCreated;
use Modules\UserManagement\Models\WorkspaceInvitation;
use Modules\UserManagement\Repositories\UserRepository;
use Modules\UserManagement\Repositories\WorkspaceInvitationRepository;
use Modules\UserManagement\Repositories\WorkspaceMemberRepository;

class WorkspaceInvitationService
{
    public function __construct(
        private WorkspaceInvitationRepository $workspaceInvitationRepository,
        private WorkspaceMemberRepository $workspaceMemberRepository,
        private UserRepository $userRepository,
        private AuthService $authService,
    ) {}

    public function invite(User $inviter, array $data): WorkspaceInvitation
    {
        $plainToken = Str::random(64);

        $invitation = $this->workspaceInvitationRepository->create([
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'invited_by' => $inviter->id,
            'token' => hash('sha256', $plainToken),
            'expires_at' => now()->addDays(7),
        ])->load(['workspace', 'role']);

        WorkspaceInvitationCreated::dispatch($invitation, $this->invitationUrl($plainToken));

        return $invitation;
    }

    public function findByToken(string $token): WorkspaceInvitation
    {
        return $this->workspaceInvitationRepository->findByPlainToken($token);
    }

    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $invitation = $this->workspaceInvitationRepository->findByPlainToken($data['token']);

            $user = $this->userRepository->create([
                'name' => $data['name'],
                'email' => $invitation->email,
                'password' => Hash::make($data['password']),
            ]);

            $this->userRepository->markEmailVerified($user);

            $this->workspaceMemberRepository->create([
                'workspace_id' => $invitation->workspace_id,
                'user_id' => $user->id,
                'role_id' => $invitation->role_id,
                'is_owner' => false,
            ]);

            $accepted = $this->workspaceInvitationRepository->markAccepted($invitation);

            return [
                'user' => $user->refresh()->load('workspaces'),
                'invitation' => $accepted,
                'token' => $this->authService->issueToken($user),
            ];
        });
    }

    private function invitationUrl(string $token): string
    {
        return config('app.frontend_url').'/invitations/accept?token='.$token;
    }
}

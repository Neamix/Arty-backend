<?php

namespace Modules\UserManagement\Services;

use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Modules\UserManagement\Repositories\UserRepository;

class GoogleAuthService
{
    public function __construct(
        private UserRepository $userRepository,
        private WorkspaceService $workspaceService,
        private AuthService $authService,
    ) {}

    public function getRedirectUrl(): string
    {
        return Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
    }

    public function handleCallback(): array
    {
        return $this->resolveOrCreate($this->normalizeSocialite(Socialite::driver('google')->stateless()->user()));
    }

    public function resolveOrCreate(array $googleUser): array
    {
        $existing = $this->userRepository->findByGoogleId($googleUser['google_id'])
            ?? $this->userRepository->findByEmail($googleUser['email']);

        if ($existing) {
            if (! $existing->google_id) {
                $this->userRepository->linkGoogle($existing, $googleUser['google_id'], $googleUser['avatar']);
            }

            return [
                'user' => $existing->refresh()->load('workspaces'),
                'token' => $this->authService->issueToken($existing),
                'created' => false,
            ];
        }

        $user = $this->authService->saveNewUser([
            'name' => $googleUser['name'],
            'email' => $googleUser['email'],
            'password' => str()->random(32),
            'workspace_name' => null,
        ]);

        $this->userRepository->linkGoogle($user, $googleUser['google_id'], $googleUser['avatar']);
        $this->userRepository->markEmailVerified($user);

        return [
            'user' => $user->refresh()->load('workspaces'),
            'token' => $this->authService->issueToken($user),
            'created' => true,
        ];
    }

    private function normalizeSocialite(SocialiteUser $user): array
    {
        return [
            'google_id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName() ?? $user->getNickname() ?? 'User',
            'avatar' => $user->getAvatar(),
        ];
    }
}

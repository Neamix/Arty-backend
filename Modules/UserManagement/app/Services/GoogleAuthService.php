<?php

namespace Modules\UserManagement\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
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

    /**
     * @return array{user: User, token: string, created: bool}
     */
    public function handleCallback(): array
    {
        return $this->resolveOrCreate($this->normalizeSocialite(Socialite::driver('google')->stateless()->user()));
    }

    /**
     * @param  array{google_id: string, email: string, name: string, avatar: ?string}  $googleUser
     * @return array{user: User, token: string, created: bool}
     */
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

        $user = DB::transaction(function () use ($googleUser) {
            $user = $this->userRepository->create([
                'name' => $googleUser['name'],
                'email' => $googleUser['email'],
                'google_id' => $googleUser['google_id'],
                'avatar' => $googleUser['avatar'],
                'email_verified_at' => now(),
            ]);

            $this->workspaceService->createForOwner($user);

            return $user;
        });

        return [
            'user' => $user->refresh()->load('workspaces'),
            'token' => $this->authService->issueToken($user),
            'created' => true,
        ];
    }

    /**
     * @return array{google_id: string, email: string, name: string, avatar: ?string}
     */
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

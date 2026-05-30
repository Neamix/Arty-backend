<?php

namespace Modules\UserManagement\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\UserManagement\Enums\OtpUsage;
use Modules\UserManagement\Exceptions\AuthException;
use Modules\UserManagement\Repositories\UserRepository;

class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
        private WorkspaceService $workspaceService,
        private OtpService $otpService,
    ) {}

    /**
     * @param  array{name: string, email: string, password: string, workspace_name?: ?string}  $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        $user = DB::transaction(function () use ($data) {
            $user = $this->userRepository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $this->workspaceService->createForOwner($user, $data['workspace_name'] ?? null);

            return $user;
        });

        return [
            'user' => $user->refresh()->load('workspaces'),
            'token' => $this->issueToken($user),
        ];
    }

    /**
     * @param  array{email: string, password: string}  $data
     * @return array{user: User, token: string}
     *
     * @throws AuthException
     */
    public function login(array $data): array
    {
        $user = $this->userRepository->findByEmail($data['email']);

        if (! $user || ! $user->password || ! Hash::check($data['password'], $user->password)) {
            throw new AuthException('Invalid credentials.');
        }

        return [
            'user' => $user->load('workspaces'),
            'token' => $this->issueToken($user),
        ];
    }

    public function logout(User $user): void
    {
        $token = $user->token();
        $token?->revoke();
    }

    /**
     * @param  array{email: string, password: string}  $data
     *
     * @throws AuthException
     */
    public function resetPassword(array $data): User
    {
        $this->otpService->ensureVerifiedWithinWindow($data['email'], OtpUsage::PasswordReset);

        $user = $this->userRepository->findByEmail($data['email']);

        if (! $user) {
            throw new AuthException('User not found.', 404);
        }

        $this->userRepository->updatePassword($user, Hash::make($data['password']));
        $this->otpService->consume($data['email'], OtpUsage::PasswordReset);

        return $user;
    }

    public function issueToken(User $user): string
    {
        return $user->createToken('api')->accessToken;
    }
}

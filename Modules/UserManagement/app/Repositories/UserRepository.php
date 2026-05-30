<?php

namespace Modules\UserManagement\Repositories;

use App\Models\User;

class UserRepository
{
    public function __construct(private User $user) {}

    public function findByEmail(string $email): ?User
    {
        return $this->user->newQuery()->where('email', $email)->first();
    }

    public function findByGoogleId(string $googleId): ?User
    {
        return $this->user->newQuery()->where('google_id', $googleId)->first();
    }

    public function create(array $attributes): User
    {
        return $this->user->newQuery()->create($attributes);
    }

    public function updatePassword(User $user, string $hashedPassword): bool
    {
        return $user->forceFill(['password' => $hashedPassword])->save();
    }

    public function linkGoogle(User $user, string $googleId, ?string $avatar = null): User
    {
        $user->forceFill([
            'google_id' => $googleId,
            'avatar' => $avatar ?? $user->avatar,
        ])->save();

        return $user;
    }
}

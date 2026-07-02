<?php

namespace Modules\UserManagement\Models;

use App\Models\Concerns\BelongsToWorkspace;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceInvitation extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'email',
        'role_id',
        'invited_by',
        'token',
        'expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (isset($filters['workspace_id'])) {
            $query->where('workspace_id', $filters['workspace_id']);
        }

        if (isset($filters['email'])) {
            $query->where('email', 'like', '%'.$filters['email'].'%');
        }

        if (isset($filters['email_exact'])) {
            $query->where('email', $filters['email_exact']);
        }

        if (isset($filters['role_id'])) {
            $query->where('role_id', $filters['role_id']);
        }

        if (isset($filters['invited_by'])) {
            $query->where('invited_by', $filters['invited_by']);
        }

        if (isset($filters['token'])) {
            $query->where('token', $filters['token']);
        }

        if (isset($filters['accepted'])) {
            $filters['accepted']
                ? $query->whereNotNull('accepted_at')
                : $query->whereNull('accepted_at');
        }

        return $query;
    }
}

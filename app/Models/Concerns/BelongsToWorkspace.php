<?php

namespace App\Models\Concerns;

use App\Models\Scopes\WorkspaceScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Modules\UserManagement\Models\Workspace;

/**
 * @mixin Model
 *
 * @method static void addGlobalScope(Scope $scope)
 * @method static void creating(callable $callback)
 * @method static void updating(callable $callback)
 */
trait BelongsToWorkspace
{
    protected static array $workspaceIdCache = [];

    public static function bootBelongsToWorkspace(): void
    {
        static::addGlobalScope(new WorkspaceScope);

        static::creating(function (Model $model): void {
            if (empty($model->workspace_id)) {
                $model->workspace_id = static::currentWorkspaceId();
            }
        });

        static::updating(function (Model $model): void {
            $workspaceId = static::currentWorkspaceId();

            if ($workspaceId !== null) {
                $model->workspace_id = $workspaceId;
            }
        });
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    protected static function currentWorkspaceId(): ?int
    {
        $userId = Auth::id();

        if ($userId === null) {
            return null;
        }

        return static::$workspaceIdCache[$userId] ??= Auth::user()->workspaces()->value('workspaces.id');
    }
}

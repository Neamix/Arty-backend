<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class WorkspaceScope implements Scope
{
    protected static array $cache = [];

    public function apply(Builder $builder, Model $model): void
    {
        if (! Auth::check()) {
            return;
        }

        $ids = static::$cache[Auth::id()] ??= Auth::user()->workspaces()->pluck('id')->all();

        $builder->whereIn($model->getTable().'.workspace_id', $ids);
    }

    public static function flush(): void
    {
        static::$cache = [];
    }
}

<?php

namespace Modules\UserManagement\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use BelongsToWorkspace;

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (isset($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }

        return $query;
    }
}

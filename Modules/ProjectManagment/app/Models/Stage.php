<?php

namespace Modules\ProjectManagment\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ProjectManagment\Observers\BoardCacheObserver;

#[ObservedBy(BoardCacheObserver::class)]
class Stage extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'project_id',
        'name',
        'sort_order',
    ];

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (isset($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (isset($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name']);
        }

        return $query;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

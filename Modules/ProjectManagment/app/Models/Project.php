<?php

namespace Modules\ProjectManagment\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\ProjectManagment\Observers\BoardCacheObserver;

#[ObservedBy(BoardCacheObserver::class)]
class Project extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'title',
        'avatar_name',
    ];

    public function form(): HasOne
    {
        return $this->hasOne(Form::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class)->orderBy('sort_order');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (isset($filters['workspace_id'])) {
            $query->where('workspace_id', $filters['workspace_id']);
        }

        if (isset($filters['title'])) {
            $query->where('title', 'like', '%'.$filters['title'].'%');
        }

        return $query;
    }
}

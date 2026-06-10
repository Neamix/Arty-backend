<?php

namespace Modules\ProjectManagement\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectStage extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'sort_order',
    ];

    public function scopeFilter(Builder $query, array $filters): void
    {
        if (isset($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
    }

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(ProjectLead::class)->orderBy('sort_order');
    }
}

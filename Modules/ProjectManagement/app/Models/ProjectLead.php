<?php

namespace Modules\ProjectManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectLead extends Model
{
    protected $fillable = [
        'project_id',
        'project_stage_id',
        'created_by',
        'sort_order',
    ];

    public function scopeFilter(Builder $query, array $request): void
    {
        if (isset($request['stage_id'])) {
            $query->where('project_stage_id', $request['stage_id']);
        }

        if (isset($request['project_id'])) {
            $query->where('project_id', $request['project_id']);
        }

        if (isset($request['field_values'])) {
            foreach ($request['field_values'] as $fieldFilter) {
               
            }
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

    public function stage(): BelongsTo
    {
        return $this->belongsTo(ProjectStage::class, 'project_stage_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProjectLeadValue::class);
    }
}

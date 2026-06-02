<?php

namespace Modules\ProjectManagement\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectLeadValue extends Model
{
    protected $fillable = [
        'project_lead_id',
        'project_form_field_id',
        'value',
    ];

    public function scopeFilter(Builder $query, array $filters): void
    {
        if (isset($filters['project_lead_id'])) {
            $query->where('project_lead_id', $filters['project_lead_id']);
        }

        if (isset($filters['project_form_field_id'])) {
            $query->where('project_form_field_id', $filters['project_form_field_id']);
        }
    }

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(ProjectLead::class, 'project_lead_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(ProjectFormField::class, 'project_form_field_id');
    }
}

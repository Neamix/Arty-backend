<?php

namespace Modules\ProjectManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectLeadValue extends Model
{
    protected $fillable = [
        'project_lead_id',
        'project_form_field_id',
        'value',
    ];

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

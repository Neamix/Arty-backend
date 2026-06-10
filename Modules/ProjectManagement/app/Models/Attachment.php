<?php

namespace Modules\ProjectManagement\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    protected $fillable = [
        'lead_id',
        'size',
        'real_name',
        'uploaded_name',
    ];

    public function scopeFilter(Builder $query, array $filters): void
    {
        if (isset($filters['lead_id'])) {
            $query->where('lead_id', $filters['lead_id']);
        }
    }

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}

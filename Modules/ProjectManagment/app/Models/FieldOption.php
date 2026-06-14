<?php

namespace Modules\ProjectManagment\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldOption extends Model
{
    protected $fillable = [
        'field_id',
        'label',
        'value',
        'sort_order',
    ];

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (isset($filters['field_id'])) {
            $query->where('field_id', $filters['field_id']);
        }

        if (isset($filters['label'])) {
            $query->where('label', 'like', '%'.$filters['label'].'%');
        }

        if (isset($filters['value'])) {
            $query->where('value', $filters['value']);
        }

        return $query;
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }
}

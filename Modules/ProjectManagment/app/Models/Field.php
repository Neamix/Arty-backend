<?php

namespace Modules\ProjectManagment\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\ProjectManagment\Enums\FieldType;

class Field extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'form_id',
        'label',
        'type',
        'is_required',
        'sort_order',
        'config',
        'default_value',
        'is_title',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => FieldType::class,
            'is_required' => 'boolean',
            'config' => 'array',
            'is_title' => 'boolean',
        ];
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (isset($filters['form_id'])) {
            $query->where('form_id', $filters['form_id']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_required'])) {
            $query->where('is_required', $filters['is_required']);
        }

        if (isset($filters['label'])) {
            $query->where('label', 'like', '%'.$filters['label'].'%');
        }

        return $query;
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(FieldOption::class)->orderBy('sort_order');
    }
}

<?php

namespace Modules\ProjectManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\ActivityLog\Concerns\LogsActivity;

class Lead extends Model
{
    use LogsActivity;

    protected $fillable = [
        'project_id',
        'stage_id',
        'created_by',
        'sort_order',
    ];

    public function scopeFilter(Builder $query, array $filters): void
    {
        if (isset($filters['stage_id'])) {
            $query->where('stage_id', $filters['stage_id']);
        }

        if (isset($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (isset($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        if (isset($filters['field_values'])) {
            foreach ($filters['field_values'] as $fieldFilter) {
                $query->whereHas('values', function (Builder $values) use ($fieldFilter): void {
                    $values
                        ->where('project_form_field_id', $fieldFilter['field_id'])
                        ->where('value', json_encode($fieldFilter['value']));
                });
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
        return $this->belongsTo(Stage::class, 'stage_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function values(): HasMany
    {
        return $this->hasMany(LeadValue::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}

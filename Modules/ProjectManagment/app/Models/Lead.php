<?php

namespace Modules\ProjectManagment\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'stage_id',
        'due_date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
        ];
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (isset($filters['project_id'])) {
            $query
                ->select('leads.*')
                ->join('stages', 'stages.id', '=', 'leads.stage_id')
                ->where('stages.project_id', $filters['project_id']);
        }

        if (isset($filters['stage_id'])) {
            $query->where('leads.stage_id', $filters['stage_id']);
        }

        if (isset($filters['due_from'])) {
            $query->where('leads.due_date', '>=', $filters['due_from']);
        }

        if (isset($filters['due_to'])) {
            $query->where('leads.due_date', '<=', $filters['due_to']);
        }

        if (! empty($filters['answers'])) {
            foreach ($filters['answers'] as $field) {
                $query->whereHas('answers', function (Builder $answers) use ($field): void {
                    $answers->where('field_id', $field['field_id']);

                    match ($field['type']) {
                        'price' => $answers->whereRaw('CAST(value AS DECIMAL(15,2)) BETWEEN ? AND ?', [$field['value'][0], $field['value'][1]]),
                        'options', 'checkout' => $answers->whereIn('value', $field['value']),
                        default => $answers->where('value', $field['value']),
                    };
                });
            }
        }

        return $query;
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(LeadAnswer::class);
    }

    public function title(): string
    {
        $titleAnswer = $this->answers->first(fn (LeadAnswer $answer) => $answer->field->is_title);

        return $titleAnswer?->value
            ?: $titleAnswer?->field->default_value
            ?: $this->answers->first()?->value
            ?: '';
    }
}

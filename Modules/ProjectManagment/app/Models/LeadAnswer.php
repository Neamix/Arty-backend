<?php

namespace Modules\ProjectManagment\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadAnswer extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id',
        'lead_id',
        'field_id',
        'value',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }
}

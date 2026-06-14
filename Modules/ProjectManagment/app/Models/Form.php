<?php

namespace Modules\ProjectManagment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    protected $fillable = [
        'project_id',
        'name',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(Field::class)->orderBy('sort_order');
    }
}

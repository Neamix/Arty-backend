<?php

namespace Modules\ProjectManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\ProjectManagement\Database\Factories\ProjectFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'card_title_field_id',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cardTitleField(): BelongsTo
    {
        return $this->belongsTo(ProjectFormField::class, 'card_title_field_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(ProjectStage::class)->orderBy('sort_order');
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(ProjectFormField::class)->orderBy('sort_order');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(ProjectLead::class);
    }

    protected static function newFactory(): ProjectFactory
    {
        return ProjectFactory::new();
    }
}

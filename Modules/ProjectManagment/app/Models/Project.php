<?php

namespace Modules\ProjectManagment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder;

// use Modules\ProjectManagment\Database\Factories\ProjectFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [];

   public function scopeFilter(Builder $builder,array $request): Builder
   {
        if (isset($request['title'])) {
            $builder->where('title','like','%'.$request['title']);
        }

        return $builder;
   }
}

<?php

namespace Modules\ProjectManagement\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\ProjectManagement\Models\Project;
use Modules\UserManagement\Models\Workspace;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'name' => fake()->words(2, true),
            'icon' => null,
            'card_title_field_id' => null,
            'created_by' => User::factory(),
        ];
    }
}

<?php

namespace Modules\ProjectManagement\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\ProjectManagement\Models\Project;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'icon' => null,
            'card_title_field_id' => null,
            'created_by' => User::factory(),
        ];
    }
}

<?php

namespace Modules\ProjectManagment\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\ProjectManagment\Enums\FieldType;
use Modules\ProjectManagment\Models\Field;
use Modules\ProjectManagment\Models\FieldOption;
use Modules\ProjectManagment\Models\Form;
use Modules\ProjectManagment\Models\Lead;
use Modules\ProjectManagment\Models\LeadAnswer;
use Modules\ProjectManagment\Models\Project;
use Modules\ProjectManagment\Models\Stage;
use Modules\UserManagement\Models\Workspace;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@artmes.test'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('Password123'),
                'email_verified_at' => now(),
            ],
        );

        $workspace = Workspace::firstOrCreate(
            ['owner_id' => $user->id, 'slug' => 'demo-workspace'],
            ['name' => 'Demo Workspace'],
        );

        $project = Project::create([
            'workspace_id' => $workspace->id,
            'title' => 'Sales Pipeline',
            'avatar_name' => 'briefcase',
        ]);

        $form = Form::create([
            'workspace_id' => $workspace->id,
            'project_id' => $project->id,
            'name' => 'Intake Form',
        ]);

        $name = Field::create([
            'workspace_id' => $workspace->id,
            'form_id' => $form->id,
            'label' => 'Full Name',
            'type' => FieldType::Text,
            'is_required' => true,
            'sort_order' => 1,
            'default_value' => 'Untitled',
            'is_title' => true,
        ]);

        $email = Field::create([
            'workspace_id' => $workspace->id,
            'form_id' => $form->id,
            'label' => 'Email',
            'type' => FieldType::Text,
            'is_required' => true,
            'sort_order' => 2,
        ]);

        $source = Field::create([
            'workspace_id' => $workspace->id,
            'form_id' => $form->id,
            'label' => 'Source',
            'type' => FieldType::Select,
            'is_required' => false,
            'sort_order' => 3,
        ]);

        $sourceValues = ['Website', 'Referral', 'Cold Call'];
        foreach ($sourceValues as $i => $value) {
            FieldOption::create([
                'workspace_id' => $workspace->id,
                'field_id' => $source->id,
                'label' => $value,
                'value' => $value,
                'sort_order' => $i + 1,
            ]);
        }

        $stages = collect(['Backlog', 'In Progress', 'Won', 'Lost'])
            ->map(fn (string $stageName, int $i) => Stage::create([
                'workspace_id' => $workspace->id,
                'project_id' => $project->id,
                'name' => $stageName,
                'sort_order' => $i + 1,
            ]));

        foreach (range(1, 100) as $i) {
            $stage = $stages[$i % $stages->count()];

            $lead = Lead::create([
                'workspace_id' => $workspace->id,
                'stage_id' => $stage->id,
                'due_date' => Carbon::now()->addDays(rand(-15, 30)),
            ]);

            $answers = [
                $name->id => 'Lead '.$i.' '.Str::random(4),
                $email->id => 'lead'.$i.'@example.com',
                $source->id => $sourceValues[array_rand($sourceValues)],
            ];

            foreach ($answers as $fieldId => $value) {
                LeadAnswer::create([
                    'workspace_id' => $workspace->id,
                    'lead_id' => $lead->id,
                    'field_id' => $fieldId,
                    'value' => $value,
                ]);
            }
        }

        $this->command->info("Seeded project #{$project->id}: {$stages->count()} stages, 100 leads. Login: demo@artmes.test / Password123");
    }
}

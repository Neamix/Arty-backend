<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX lead_answers_field_value_index ON lead_answers (field_id, value(191))');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX lead_answers_field_value_index ON lead_answers');
    }
};

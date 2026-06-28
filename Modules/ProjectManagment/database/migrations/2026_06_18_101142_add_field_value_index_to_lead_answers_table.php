<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::table('lead_answers', function (Blueprint $table) {
                $table->index(['field_id', 'value'], 'lead_answers_field_value_index');
            });

            return;
        }

        DB::statement('CREATE INDEX lead_answers_field_value_index ON lead_answers (field_id, value(191))');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::table('lead_answers', function (Blueprint $table) {
                $table->dropIndex('lead_answers_field_value_index');
            });

            return;
        }

        DB::statement('DROP INDEX lead_answers_field_value_index ON lead_answers');
    }
};

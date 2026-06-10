<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('workspace_id')->after('id')->constrained('workspaces')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        $hasFk = DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_NAME = 'projects' AND TABLE_SCHEMA = DATABASE()
             AND COLUMN_NAME = 'workspace_id' AND REFERENCED_TABLE_NAME IS NOT NULL"
        );

        Schema::table('projects', function (Blueprint $table) use ($hasFk) {
            if (! empty($hasFk)) {
                $table->dropForeign(['workspace_id']);
            }
            $table->dropColumn('workspace_id');
        });
    }
};

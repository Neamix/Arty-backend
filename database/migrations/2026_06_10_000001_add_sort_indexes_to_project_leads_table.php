<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_leads', function (Blueprint $table) {
            $table->index(['project_stage_id', 'sort_order', 'id'], 'project_leads_stage_sort_index');
            $table->index(['project_id', 'sort_order', 'id'], 'project_leads_project_sort_index');
        });
    }

    public function down(): void
    {
        Schema::table('project_leads', function (Blueprint $table) {
            $table->dropIndex('project_leads_stage_sort_index');
            $table->dropIndex('project_leads_project_sort_index');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->index(['stage_id', 'sort_order', 'id'], 'leads_stage_sort_index');
            $table->index(['project_id', 'sort_order', 'id'], 'leads_project_sort_index');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_stage_sort_index');
            $table->dropIndex('leads_project_sort_index');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_lead_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_lead_id')->constrained('project_leads')->cascadeOnDelete();
            $table->foreignId('project_form_field_id')->constrained('project_form_fields')->cascadeOnDelete();
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique(['project_lead_id', 'project_form_field_id'], 'lead_field_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_lead_values');
    }
};

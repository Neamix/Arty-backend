<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('fields')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->index('workspace_id');
            $table->unique(['lead_id', 'field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_answers');
    }
};

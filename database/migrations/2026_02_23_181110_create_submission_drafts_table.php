<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_assignment_id')->constrained('form_assignments')->cascadeOnDelete();
            $table->json('draft_data');
            $table->timestamp('last_synced_at');
            $table->timestamps();

            $table->unique(['form_id', 'user_id', 'form_assignment_id']);
            $table->index(['user_id', 'form_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_drafts');
    }
};

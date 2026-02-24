<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('due_at')->nullable();
            $table->enum('status', ['pending', 'completed', 'expired'])->default('pending');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index(['form_id', 'assigned_to', 'status', 'due_at']);
        });

        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->enum('report_type', ['type1', 'type2']);
            $table->enum('status', ['draft', 'submitted', 'returned', 'corrected'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'form_id', 'status']);
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('form_fields')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->index(['submission_id', 'field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('assignments');
    }
};

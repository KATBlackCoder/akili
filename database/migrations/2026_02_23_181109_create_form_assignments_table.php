<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->enum('scope_type', ['role', 'individual']);
            $table->enum('scope_role', ['superviseur', 'employe', 'both'])->nullable();
            $table->timestamp('due_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['form_id', 'scope_type', 'is_active']);
        });

        Schema::create('form_assignment_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['form_assignment_id', 'user_id']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_assignment_users');
        Schema::dropIfExists('form_assignments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->enum('scope', ['partial', 'full']);
            $table->enum('status', ['pending', 'corrected'])->default('pending');
            $table->timestamp('corrected_at')->nullable();
            $table->timestamps();
        });

        Schema::create('correction_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correction_id')->constrained('submission_corrections')->cascadeOnDelete();
            $table->foreignId('field_id')->nullable()->constrained('form_fields')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('form_sections')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correction_fields');
        Schema::dropIfExists('submission_corrections');
    }
};

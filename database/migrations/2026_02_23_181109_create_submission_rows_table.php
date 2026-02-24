<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->integer('row_index');
            $table->timestamps();

            $table->index(['submission_id', 'row_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_rows');
    }
};

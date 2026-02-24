<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->foreignId('row_id')
                ->nullable()
                ->constrained('submission_rows')
                ->cascadeOnDelete()
                ->after('submission_id');
        });
    }

    public function down(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropForeign(['row_id']);
            $table->dropColumn('row_id');
        });
    }
};

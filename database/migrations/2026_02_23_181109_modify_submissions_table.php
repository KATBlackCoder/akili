<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->foreignId('form_assignment_id')
                ->nullable()
                ->constrained('form_assignments')
                ->nullOnDelete()
                ->after('assignment_id');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['form_assignment_id']);
            $table->dropColumn('form_assignment_id');
        });
    }
};

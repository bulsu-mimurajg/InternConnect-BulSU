<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('hte_question_id')
                  ->nullable()
                  ->after('subcategory_id')
                  ->constrained('questions')
                  ->nullOnDelete();
            $table->index('hte_question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['hte_question_id']);
            $table->dropIndex(['hte_question_id']);
            $table->dropColumn('hte_question_id');
        });
    }
};

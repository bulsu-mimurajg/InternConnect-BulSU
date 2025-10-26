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
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('questions', 'question_type')) {
                $table->enum('question_type', [
                    'multiple_choice',
                    'true_false',
                    'essay',
                    'enumeration',
                    'identification'
                ])->default('multiple_choice')->after('question');
            }
            
            if (!Schema::hasColumn('questions', 'points')) {
                $table->decimal('points', 5, 2)->default(1.00)->after('question_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['question_type', 'points']);
        });
    }
};

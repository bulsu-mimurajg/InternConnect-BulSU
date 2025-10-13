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
        Schema::table('students', function (Blueprint $table) {
            // Add foreign key to internship_seasons table
            $table->foreignId('internship_season_id')->nullable()->constrained()->onDelete('set null');
            
            // Add index for performance
            $table->index('internship_season_id');
        });

        // Add unique constraint to allow reuse of archived student numbers
        // This ensures only one active student can have a specific student_number
        Schema::table('students', function (Blueprint $table) {
            $table->unique(['student_number', 'is_active'], 'unique_active_student_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('unique_active_student_number');
            $table->dropForeign(['internship_season_id']);
            $table->dropIndex(['internship_season_id']);
            $table->dropColumn('internship_season_id');
        });
    }
};
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
        Schema::table('deadlines', function (Blueprint $table) {
            // Add foreign key to internship_seasons table
            $table->foreignId('internship_season_id')->nullable()->constrained()->onDelete('cascade');
            
            // Add index for performance
            $table->index('internship_season_id');
        });

        // Update the enum to include 'archive_students' category
        DB::statement("ALTER TABLE deadlines MODIFY COLUMN category ENUM(
            'hte_assessment_form',
            'student_verification', 
            'student_assessment_form',
            'internship_placement',
            'archive_students'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deadlines', function (Blueprint $table) {
            $table->dropForeign(['internship_season_id']);
            $table->dropIndex(['internship_season_id']);
            $table->dropColumn('internship_season_id');
        });

        // Revert the enum to original values
        DB::statement("ALTER TABLE deadlines MODIFY COLUMN category ENUM(
            'hte_assessment_form',
            'student_verification',
            'student_assessment_form', 
            'internship_placement'
        ) NOT NULL");
    }
};
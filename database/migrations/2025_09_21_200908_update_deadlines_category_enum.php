<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the ENUM values to include the new categories
        DB::statement("ALTER TABLE deadlines MODIFY COLUMN category ENUM('student_verification', 'student_assessment_form', 'hte_assessment_form', 'sip_endorsement', 'student_placements_by_hte')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM values
        DB::statement("ALTER TABLE deadlines MODIFY COLUMN category ENUM('student_verification', 'student_assessment_form', 'hte_assessment_form')");
    }
};
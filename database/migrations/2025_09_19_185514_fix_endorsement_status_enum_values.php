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
        // First, update any existing 'approved' values to 'endorsed' to match the new enum
        DB::statement("UPDATE student_matches SET endorsement_status = 'endorsed' WHERE endorsement_status = 'approved'");
        
        // Then update the endorsement_status enum to include 'endorsed' instead of 'approved'
        DB::statement("ALTER TABLE student_matches MODIFY COLUMN endorsement_status ENUM('pending', 'endorsed', 'rejected') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, temporarily allow 'approved' in the enum
        DB::statement("ALTER TABLE student_matches MODIFY COLUMN endorsement_status ENUM('pending', 'endorsed', 'rejected', 'approved') DEFAULT 'pending'");
        
        // Then update any existing 'endorsed' values to 'approved' to match the old enum
        DB::statement("UPDATE student_matches SET endorsement_status = 'approved' WHERE endorsement_status = 'endorsed'");
        
        // Finally, revert back to the original enum values (removing 'endorsed')
        DB::statement("ALTER TABLE student_matches MODIFY COLUMN endorsement_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
    }
};
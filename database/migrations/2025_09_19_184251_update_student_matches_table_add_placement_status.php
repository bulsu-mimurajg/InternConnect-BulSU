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
        Schema::table('student_matches', function (Blueprint $table) {
            // Check if status column exists and rename it to endorsement_status
            if (Schema::hasColumn('student_matches', 'status')) {
                $table->renameColumn('status', 'endorsement_status');
            }
            
            // Add new placement_status column if it doesn't exist
            if (!Schema::hasColumn('student_matches', 'placement_status')) {
                $table->enum('placement_status', ['pending', 'approved', 'rejected'])->default('pending')->after('endorsement_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_matches', function (Blueprint $table) {
            // Drop the placement_status column if it exists
            if (Schema::hasColumn('student_matches', 'placement_status')) {
                $table->dropColumn('placement_status');
            }
            
            // Rename endorsement_status back to status if endorsement_status exists
            if (Schema::hasColumn('student_matches', 'endorsement_status')) {
                $table->renameColumn('endorsement_status', 'status');
            }
        });
    }
};
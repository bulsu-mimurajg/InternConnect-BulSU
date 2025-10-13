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
        // Update the status enum to include 'inactive'
        DB::statement("ALTER TABLE deadlines MODIFY COLUMN status ENUM('inactive', 'active', 'expired') DEFAULT 'inactive'");
        
        // Update existing deadlines to have proper status based on dates
        DB::statement("
            UPDATE deadlines 
            SET status = CASE 
                WHEN end_date <= NOW() THEN 'expired'
                WHEN start_date > NOW() THEN 'inactive'
                ELSE 'active'
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE deadlines MODIFY COLUMN status ENUM('active', 'expired') DEFAULT 'active'");
        
        // Convert 'inactive' status back to 'active' (best approximation)
        DB::statement("UPDATE deadlines SET status = 'active' WHERE status = 'inactive'");
    }
};

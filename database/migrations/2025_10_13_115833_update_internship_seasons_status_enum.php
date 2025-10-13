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
        // Update the enum to include 'inactive' and change default to 'inactive'
        DB::statement("ALTER TABLE internship_seasons MODIFY COLUMN status ENUM('inactive', 'active', 'completed', 'archived') DEFAULT 'inactive'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum
        DB::statement("ALTER TABLE internship_seasons MODIFY COLUMN status ENUM('active', 'completed', 'archived') DEFAULT 'active'");
    }
};
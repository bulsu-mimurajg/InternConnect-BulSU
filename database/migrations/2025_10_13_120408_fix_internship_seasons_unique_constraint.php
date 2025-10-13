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
        // Remove the problematic unique constraint
        Schema::table('internship_seasons', function (Blueprint $table) {
            $table->dropUnique('unique_active_season');
        });
        
        // Add a proper unique constraint that only applies to active seasons
        // This will be enforced at the application level since MySQL doesn't support partial unique indexes
        Schema::table('internship_seasons', function (Blueprint $table) {
            // We'll enforce this constraint in the application layer instead
            // by checking in the InternshipSeasonService
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add the unique constraint (though it was problematic)
        Schema::table('internship_seasons', function (Blueprint $table) {
            $table->unique(['status'], 'unique_active_season')->where('status', 'active');
        });
    }
};
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
        Schema::table('users', function (Blueprint $table) {
            // Drop the unique constraint on username
            $table->dropUnique(['username']);
            
            // Add a regular index for performance (non-unique)
            $table->index('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the non-unique index
            $table->dropIndex(['username']);
            
            // Re-add the unique constraint
            $table->unique('username');
        });
    }
};
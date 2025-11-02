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
        Schema::table('categories', function (Blueprint $table) {
            // Add category_type to distinguish between technical and soft skills
            $table->enum('category_type', ['technical', 'soft_skills'])
                ->default('technical')
                ->after('category_name');
        });

        // Update existing categories - all current ones are technical
        // Soft skills categories will be added via seeder
        DB::table('categories')->update(['category_type' => 'technical']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('category_type');
        });
    }
};

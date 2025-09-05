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
        Schema::create('questions', function (Blueprint $table) {
            $table->id(); // Standard auto-incrementing primary key
            $table->text('question'); // TEXT NOT NULL
            $table->enum('access', ['HTE', 'Student']); // ENUM('HTE', 'Student')
            $table->boolean('is_active')->default(true); // Default TRUE
            
            // Foreign key referencing sub_categories.id
            $table->foreignId('subcategory_id')
                  ->constrained('sub_categories')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};

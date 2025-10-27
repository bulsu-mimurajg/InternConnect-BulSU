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
        Schema::create('assessment_grading_criteria', function (Blueprint $table) {
            $table->id();

            // Link to category or subcategory (nullable - can be global)
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('categories')
                  ->cascadeOnDelete();

            $table->foreignId('subcategory_id')
                  ->nullable()
                  ->constrained('sub_categories')
                  ->cascadeOnDelete();

            // Score range
            $table->decimal('min_score', 5, 2)->comment('Minimum score (e.g., 85.00)');
            $table->decimal('max_score', 5, 2)->comment('Maximum score (e.g., 100.00)');

            // Grade information
            $table->string('grade_label')->comment('e.g., "A", "Advanced", "Proficient"');
            $table->string('grade_code')->nullable()->comment('Short code: e.g., "A", "B+", "ADV"');
            $table->decimal('grade_point', 3, 2)->nullable()->comment('GPA equivalent: e.g., 4.0, 3.5');

            // Description and feedback
            $table->text('description')->nullable()->comment('Description of this grade level');
            $table->text('feedback_template')->nullable()->comment('Feedback message template');

            // Display settings
            $table->string('color_code')->nullable()->comment('Hex color for UI: e.g., #4CAF50');
            $table->integer('display_order')->default(0)->comment('Order to display grades');

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes for performance
            $table->index(['category_id', 'is_active']);
            $table->index(['subcategory_id', 'is_active']);
            $table->index(['min_score', 'max_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_grading_criteria');
    }
};


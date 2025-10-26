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
        if (Schema::hasTable('quiz_attempts')) {
            return;
        }
        
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to students table
            $table->foreignId('student_id')
                  ->constrained('students')
                  ->cascadeOnDelete();
            
            // Foreign key to questions table
            $table->foreignId('question_id')
                  ->constrained('questions')
                  ->cascadeOnDelete();
            
            // Foreign key to answers table (for multiple choice, true/false, identification)
            $table->foreignId('selected_answer_id')
                  ->nullable()
                  ->constrained('answers')
                  ->nullOnDelete();
            
            // For text-based responses (essay, enumeration)
            $table->text('text_response')->nullable();
            
            // Whether the answer was correct (auto-set for MC/TF/ID, manually for essay/enum)
            $table->boolean('is_correct')->nullable();
            
            // Points earned for this question
            $table->decimal('points_earned', 5, 2)->nullable();
            $table->decimal('points_possible', 5, 2)->nullable();
            
            // Manual grading support for essay/enumeration
            $table->foreignId('graded_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->text('feedback')->nullable();
            
            // Tracking
            $table->timestamp('submitted_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['student_id', 'question_id']);
            $table->index(['student_id', 'submitted_at']);
            $table->unique(['student_id', 'question_id']); // One answer per question per student
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};

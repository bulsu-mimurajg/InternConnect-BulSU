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
            Schema::table('quiz_attempts', function (Blueprint $table) {
                // Student who took the quiz
                if (!Schema::hasColumn('quiz_attempts', 'student_id')) {
                    $table->foreignId('student_id')
                          ->after('id')
                          ->constrained('students')
                          ->cascadeOnDelete();
                }

                // Question being answered
                if (!Schema::hasColumn('quiz_attempts', 'question_id')) {
                    $table->foreignId('question_id')
                          ->after('student_id')
                          ->constrained('questions')
                          ->cascadeOnDelete();
                }

                // Student's submitted answer
                if (!Schema::hasColumn('quiz_attempts', 'student_answer')) {
                    $table->text('student_answer')->nullable()->after('question_id');
                }

                // Selected answer ID (for multiple choice)
                if (!Schema::hasColumn('quiz_attempts', 'selected_answer_id')) {
                    $table->foreignId('selected_answer_id')
                          ->nullable()
                          ->after('student_answer')
                          ->constrained('answers')
                          ->nullOnDelete();
                }

                // Whether the answer was correct
                if (!Schema::hasColumn('quiz_attempts', 'is_correct')) {
                    $table->boolean('is_correct')->nullable()->after('selected_answer_id');
                }

                // Points earned for this question
                if (!Schema::hasColumn('quiz_attempts', 'points_earned')) {
                    $table->decimal('points_earned', 5, 2)->default(0)->after('is_correct');
                }

                // When the answer was submitted
                if (!Schema::hasColumn('quiz_attempts', 'submitted_at')) {
                    $table->timestamp('submitted_at')->nullable()->after('points_earned');
                }

                // Attempt number (for retries)
                if (!Schema::hasColumn('quiz_attempts', 'attempt_number')) {
                    $table->integer('attempt_number')->default(1)->after('submitted_at');
                }
            });

            // Add composite index for performance
            Schema::table('quiz_attempts', function (Blueprint $table) {
                if (!Schema::hasColumn('quiz_attempts', 'student_id')) {
                    $table->index(['student_id', 'question_id', 'attempt_number']);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('quiz_attempts')) {
            Schema::table('quiz_attempts', function (Blueprint $table) {
                $table->dropIndex(['student_id', 'question_id', 'attempt_number']);
            });

            Schema::table('quiz_attempts', function (Blueprint $table) {
                if (Schema::hasColumn('quiz_attempts', 'attempt_number')) {
                    $table->dropColumn('attempt_number');
                }
                if (Schema::hasColumn('quiz_attempts', 'submitted_at')) {
                    $table->dropColumn('submitted_at');
                }
                if (Schema::hasColumn('quiz_attempts', 'points_earned')) {
                    $table->dropColumn('points_earned');
                }
                if (Schema::hasColumn('quiz_attempts', 'is_correct')) {
                    $table->dropColumn('is_correct');
                }
                if (Schema::hasColumn('quiz_attempts', 'selected_answer_id')) {
                    $table->dropForeign(['selected_answer_id']);
                    $table->dropColumn('selected_answer_id');
                }
                if (Schema::hasColumn('quiz_attempts', 'student_answer')) {
                    $table->dropColumn('student_answer');
                }
                if (Schema::hasColumn('quiz_attempts', 'question_id')) {
                    $table->dropForeign(['question_id']);
                    $table->dropColumn('question_id');
                }
                if (Schema::hasColumn('quiz_attempts', 'student_id')) {
                    $table->dropForeign(['student_id']);
                    $table->dropColumn('student_id');
                }
            });
        }
    }
};


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
        if (Schema::hasTable('answers')) {
            Schema::table('answers', function (Blueprint $table) {
                if (!Schema::hasColumn('answers', 'question_id')) {
                    $table->foreignId('question_id')
                          ->after('id')
                          ->constrained('questions')
                          ->cascadeOnDelete();
                }

                if (!Schema::hasColumn('answers', 'answer_text')) {
                    $table->text('answer_text')->after('question_id');
                }

                if (!Schema::hasColumn('answers', 'is_correct')) {
                    $table->boolean('is_correct')->default(false)->after('answer_text');
                }

                if (!Schema::hasColumn('answers', 'display_order')) {
                    $table->integer('display_order')->default(0)->after('is_correct');
                }
            });

            // Add index if it doesn't exist
            if (!Schema::hasColumn('answers', 'question_id')) {
                Schema::table('answers', function (Blueprint $table) {
                    $table->index(['question_id', 'is_correct']);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('answers')) {
            Schema::table('answers', function (Blueprint $table) {
                if (Schema::hasColumn('answers', 'display_order')) {
                    $table->dropColumn('display_order');
                }

                if (Schema::hasColumn('answers', 'is_correct')) {
                    $table->dropColumn('is_correct');
                }

                if (Schema::hasColumn('answers', 'answer_text')) {
                    $table->dropColumn('answer_text');
                }

                if (Schema::hasColumn('answers', 'question_id')) {
                    $table->dropForeign(['question_id']);
                    $table->dropColumn('question_id');
                }
            });
        }
    }
};


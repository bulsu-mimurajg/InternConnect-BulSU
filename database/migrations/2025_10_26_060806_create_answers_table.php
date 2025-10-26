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
            return;
        }
        
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to questions table
            $table->foreignId('question_id')
                  ->constrained('questions')
                  ->cascadeOnDelete();
            
            // The answer text/choice
            $table->text('answer_text');
            
            // Boolean to mark correct answer (for multiple choice, true/false, identification, enumeration)
            $table->boolean('is_correct')->default(false);
            
            // Order for displaying answer choices
            $table->integer('display_order')->default(0);
            
            $table->timestamps();
            
            // Index for performance
            $table->index(['question_id', 'is_correct']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};

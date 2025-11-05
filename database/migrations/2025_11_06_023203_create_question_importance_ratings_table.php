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
        Schema::create('question_importance_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hte_id')->constrained('htes')->cascadeOnDelete();
            $table->foreignId('internship_id')->constrained('internships')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->comment('Rating from 1-5, where 5 is most important');
            $table->timestamps();
            
            // Unique constraint to prevent duplicate ratings for the same question
            $table->unique(['hte_id', 'internship_id', 'question_id'], 'unique_hte_internship_question_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_importance_ratings');
    }
};

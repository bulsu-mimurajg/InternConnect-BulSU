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
        Schema::create('hte_assessment_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hte_id')->constrained('htes')->cascadeOnDelete();
            $table->foreignId('internship_id')->constrained('internships')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->tinyInteger('response')->comment('Likert scale response 1-5');
            $table->timestamps();

            // Ensure one response per HTE per internship per question
            $table->unique(['hte_id', 'internship_id', 'question_id'], 'hte_internship_question_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hte_assessment_responses');
    }
};


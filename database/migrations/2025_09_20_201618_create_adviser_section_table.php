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
        Schema::create('adviser_section', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adviser_id')->constrained('advisers')->onDelete('cascade');
            $table->unsignedInteger('section_id');
            $table->timestamps();

            // Foreign key constraint for section_id
            $table->foreign('section_id')->references('section_id')->on('sections')->onDelete('cascade');
            
            // Ensure unique combination of adviser and section
            $table->unique(['adviser_id', 'section_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adviser_section');
    }
};
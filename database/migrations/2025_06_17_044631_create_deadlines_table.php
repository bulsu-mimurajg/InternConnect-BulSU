<?php

use App\Models\InternshipSeason;
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
        Schema::create('deadlines', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('category', [
                'hte_assessment_form',
                'student_verification',
                'student_assessment_form',
                'internship_placement',
                'archive_students',
            ]);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->foreignIdFor(InternshipSeason::class)->nullable()
                ->constrained()
                ->onDelete('cascade');

            // Index for performance
            $table->index('internship_season_id');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deadlines');
    }
};

<?php

use App\Models\Student;
use App\Models\Internship;
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
        Schema::create('endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Student::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Internship::class)->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'endorsed', 'rejected', 'approved'])->default('pending');
            $table->decimal('compatibility_score', 5, 2);
            $table->text('notes')->nullable();
            $table->timestamp('endorsement_date')->nullable();
            $table->timestamps();
            
            // Ensure unique endorsement per student-internship pair
            $table->unique(['student_id', 'internship_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('endorsements');
    }
};
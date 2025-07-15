<?php

use App\Models\Internship;
use App\Models\Student;
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
        Schema::create('student_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Student::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Internship::class)->constrained()->cascadeOnDelete();
            $table->unsignedInteger('choice_rank');
            $table->enum('admin_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('choice_date');
            $table->boolean('auto_assigned')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_choices');
    }
};

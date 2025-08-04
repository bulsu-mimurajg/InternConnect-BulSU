<?php

use App\Models\Internship;
use App\Models\Student;
use App\Models\SubCategory;
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
        Schema::create('student_score', function (Blueprint $table) {
            $table->foreignIdFor(Student::class);
            $table->foreignIdFor(SubCategory::class);
            $table->decimal('score', 5, 2); // 5 total digits, 2 decimal places
            $table->timestamps();
            
            $table->primary(['student_id', 'sub_category_id']);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_score');
    }
};

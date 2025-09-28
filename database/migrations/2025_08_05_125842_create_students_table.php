<?php

use App\Models\User;
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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->string('student_number', 20);
            $table->string('first_name', 50);
            $table->string('middle_name', 50);
            $table->string('last_name', 50);
            $table->string('phone', 20);
            $table->unsignedInteger('section_id');
            $table->string('specialization', 50);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_submit')->default(false);
            $table->boolean('is_placed')->default(false);
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('section_id')->references('section_id')->on('sections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

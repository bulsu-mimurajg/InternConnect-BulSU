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
        Schema::create('unplaced_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('reason');
            $table->boolean('requires_manual_intervention')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->unique('student_id');
            $table->index(['requires_manual_intervention', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unplaced_students');
    }
};

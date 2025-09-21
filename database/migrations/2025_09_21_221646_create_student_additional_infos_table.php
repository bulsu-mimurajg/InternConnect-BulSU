<?php

use App\Models\AdditionalInfo;
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
        Schema::create('student_additional_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AdditionalInfo::class);
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('info');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_additional_infos');
    }
};

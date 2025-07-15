<?php

use App\Models\Competency;
use App\Models\HTE;
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
        Schema::create('criterias', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(HTE::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Competency::class)->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weight');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criterias');
    }
};

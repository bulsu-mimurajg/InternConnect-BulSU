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
        Schema::create('HTE', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->string('company_name', 100);
            $table->string('company_address', 255);
            $table->string('company_email', 100);
            $table->string('cperson_fname', 50);
            $table->string('cperson_lname', 50);
            $table->string('cperson_position', 50);
            $table->string('cperson_contactnum', 50);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('HTE');
    }
};

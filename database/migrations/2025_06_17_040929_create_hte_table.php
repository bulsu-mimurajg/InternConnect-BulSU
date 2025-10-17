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
        Schema::create('htes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->string('company_name', 100)->nullable();
            $table->string('company_address', 255)->nullable();
            $table->string('company_email', 100)->nullable();
            $table->string('cperson_fname', 50)->nullable();
            $table->string('cperson_lname', 50)->nullable();
            $table->string('cperson_position', 50)->nullable();
            $table->string('cperson_contactnum', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_submit')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('htes');
    }
};

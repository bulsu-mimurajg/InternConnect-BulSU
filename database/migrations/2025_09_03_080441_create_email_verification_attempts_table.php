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
        Schema::create('email_verification_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->integer('attempt_count')->default(1);
            $table->timestamp('last_attempt_at');
            $table->timestamp('next_attempt_allowed_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            // Index for faster lookups
            $table->index(['email', 'last_attempt_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_verification_attempts');
    }
};

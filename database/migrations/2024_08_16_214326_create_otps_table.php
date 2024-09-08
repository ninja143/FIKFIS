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
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('otp', 32); // OTP value
            $table->timestamp('expires_at'); // Expiry time
            $table->enum('contact_type', ['email', 'phone'])->nullable();
            $table->string('contact_value'); // Email or phone number
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key to users table
            $table->enum('device_type', ['ios', 'android', 'web', 'other']);
            $table->enum('invoked', [0, 1])->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};

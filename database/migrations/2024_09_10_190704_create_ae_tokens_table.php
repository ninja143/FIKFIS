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
        Schema::create('ae_tokens', function (Blueprint $table) {
            $table->id(); 
            $table->string('havana_id')->unique();
            $table->bigInteger('user_id');
            $table->string('user_nick');
            $table->string('account_platform');
            $table->string('account');
            $table->string('locale');
            $table->string('sp');  // Assuming SP stands for "Sales Person"
            $table->string('seller_id');
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('expire_time')->nullable();;
            $table->timestamp('refresh_expires_in')->nullable();;  // Renamed to singular for clarity
            $table->bigInteger('refresh_token_valid_time');  // Assuming a large integer for timestamp
            $table->string('code');
            $table->string('request_id')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ae_tokens');
    }
};

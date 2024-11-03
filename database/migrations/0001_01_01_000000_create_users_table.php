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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('tid')->unique(); // Telegram ID
            $table->string('name')->nullable(); // Short name or username
            $table->string('full_name')->nullable(); // Full name
            $table->string('user_id')->unique(); // Unique user identifier
            $table->string('channel_from',255)->nullable(); // Channel or chat source
            $table->longText('chat_id')->nullable(); // Channel or chat source
            $table->boolean('message')->default(false); // Message indicator
            $table->decimal('balance', 10, 2)->default(0); // User balance
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

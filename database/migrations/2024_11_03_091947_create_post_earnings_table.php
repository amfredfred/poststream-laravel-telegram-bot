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
        Schema::create('post_earnings', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('post_id')->constrained()->onDelete('cascade'); // Foreign key referencing posts
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key referencing users
            $table->decimal('point', 10, 2)->default(0.00); // Column for earning points as decimal
            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_earnings');
    }
};

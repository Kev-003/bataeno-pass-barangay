<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained('houses')->cascadeOnDelete();
            $table->unsignedBigInteger('household_head_id')->nullable();
            $table->string('ownership'); // Owned, Rented
            $table->decimal('monthly_utility_expense', 10, 2)->nullable();
            $table->decimal('total_income', 12, 2)->nullable();
            $table->timestamp('expires_at')->nullable(); // If household dissolves
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('households');
    }
};

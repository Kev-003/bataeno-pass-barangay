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
        Schema::create('tricycle_clearances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('document_transactions')->cascadeOnDelete();
            $table->unsignedBigInteger('new_owner_id')->nullable(); // No FK to allow external owners
            $table->unsignedBigInteger('requested_for_id')->nullable();
            $table->string('purpose');
            $table->string('body_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tricycle_clearances');
    }
};

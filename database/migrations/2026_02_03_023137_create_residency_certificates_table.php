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
        Schema::create('residency_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('document_transactions')->cascadeOnDelete();
            $table->string('requested_for');
            $table->integer('length_of_residence'); // In years or months (handle in logic)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residency_certificates');
    }
};

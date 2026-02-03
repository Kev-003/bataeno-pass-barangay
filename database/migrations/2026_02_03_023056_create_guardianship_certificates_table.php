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
        Schema::create('guardianship_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('document_transactions')->cascadeOnDelete();
            $table->string('guardian_id')->nullable(); // Might be a string ID from another system
            $table->string('relationship');
            $table->foreignId('address_id')->constrained('barangays'); // Using barangay_id as address locator
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardianship_certificates');
    }
};

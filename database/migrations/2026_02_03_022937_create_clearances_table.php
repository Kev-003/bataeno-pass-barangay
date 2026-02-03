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
        Schema::create('clearances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('document_transactions')->cascadeOnDelete();
            $table->string('gender');
            $table->string('civil_status');
            $table->string('housing_unit')->nullable();
            $table->string('street');
            $table->string('subdivision')->nullable();
            $table->string('community_tax_id')->nullable();
            $table->string('purpose');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clearances');
    }
};

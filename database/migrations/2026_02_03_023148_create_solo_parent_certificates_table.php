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
        Schema::create('solo_parent_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('document_transactions')->cascadeOnDelete();
            $table->string('solo_parent_name');
            $table->integer('no_of_child');
            $table->foreignId('address_id')->constrained('barangays');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solo_parent_certificates');
    }
};

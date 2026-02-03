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
        Schema::create('document_type_properties', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., BRGY_CLR
            $table->string('name');          // e.g., Barangay Clearance
            $table->text('description')->nullable();
            $table->decimal('default_fee', 10, 2)->default(0);
            $table->integer('validity_days')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_type_properties');
    }
};

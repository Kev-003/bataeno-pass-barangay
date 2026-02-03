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
        Schema::create('document_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_type_id')->constrained('document_type_properties')->cascadeOnDelete();
            $table->foreignId('requirement_id')->constrained('document_requirements_definitions')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_rules');
    }
};

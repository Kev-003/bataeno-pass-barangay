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
        Schema::table('barangay_terms', function (Blueprint $table) {
            // Ensure the column type matches Spatie's roles.id (unsignedBigInteger)
            $table->unsignedBigInteger('position_id')->change();

            // Add the foreign key constraint to roles(id)
            $table->foreign('position_id')
                ->references('id')
                ->on('roles')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangay_terms', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
        });
    }
};

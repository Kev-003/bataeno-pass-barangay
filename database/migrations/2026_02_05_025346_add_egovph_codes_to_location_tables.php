<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * This migration adds eGovPH codes to municipalities and barangays tables.
     * These codes will be used as the primary identifiers for linking with Bataeno Pass data.
     */
    public function up(): void
    {


        // Add eGovPH codes to barangays table
        Schema::table('barangays', function (Blueprint $table) {
            $table->string('barangay_code', 20)->unique()->nullable()->after('id');
            $table->string('municity_code', 20)->nullable()->after('barangay_code');
            $table->string('province_code', 20)->nullable()->after('municity_code');
            $table->string('region_code', 20)->nullable()->after('province_code');

            // Index for fast lookups
            $table->index('barangay_code');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('municipalities', function (Blueprint $table) {
            $table->dropIndex(['municity_code']);
            $table->dropColumn([
                'municity_code',
                'province_code',
                'province_name',
                'region_code',
                'region_name',
            ]);
        });

        Schema::table('barangays', function (Blueprint $table) {
            $table->dropIndex(['barangay_code']);
            $table->dropIndex(['municity_code']);
            $table->dropColumn([
                'barangay_code',
                'municity_code',
                'province_code',
                'region_code',
            ]);
        });
    }
};

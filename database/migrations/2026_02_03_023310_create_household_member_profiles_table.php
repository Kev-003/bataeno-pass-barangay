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
        Schema::create('household_member_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->string('role'); // Head, Member
            $table->string('membership_type'); // Resident, Boarder
            $table->string('presence_status'); // Present, OFW
            $table->string('economic_contribution')->nullable();
            $table->decimal('monthly_income', 10, 2)->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('household_member_profiles');
    }
};

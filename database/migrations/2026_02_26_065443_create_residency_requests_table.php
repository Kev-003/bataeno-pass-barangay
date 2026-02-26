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
        Schema::create('residency_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('barangay_id')->constrained('barangays');

            // House Details
            $table->string('housing_unit')->nullable();
            $table->string('street');
            $table->string('subdivision')->nullable();

            // Profile info
            $table->string('role')->default('Head'); // Head, Member
            $table->string('membership_type')->default('Primary'); // Primary, Boarder, etc.
            $table->string('ownership')->default('Owned'); // Owned, Rented, etc.

            $table->string('status')->default('Pending'); // Pending, Approved, Rejected, Cancelled
            $table->text('rejection_reason')->nullable();

            $table->foreignId('approver_id')->nullable()->constrained('users');
            $table->timestamp('actioned_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residency_requests');
    }
};

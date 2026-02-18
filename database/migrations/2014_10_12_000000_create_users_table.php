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
        Schema::disableForeignKeyConstraints();
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('family_id')->nullable()->constrained('families')->nullOnDelete();

            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();

            $table->date('date_of_birth');
            $table->string('place_of_birth');
            $table->string('gender');
            $table->string('civil_status');

            $table->string('blood_type')->nullable();
            $table->string('occupation')->nullable();
            $table->timestamp('registered_at')->useCurrent();

            $table->string('municity_code')->nullable();
            $table->string('municity_name')->nullable();
            $table->string('barangay_code')->nullable();
            $table->string('barangay_name')->nullable();

            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->json('egov_data')->nullable();

            // These were in your Model's fillable list but missing here:
            $table->text('profile_photos')->nullable();
            $table->text('digital_signature')->nullable();

            // --- END NEW FIELDS ---

            $table->rememberToken();
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

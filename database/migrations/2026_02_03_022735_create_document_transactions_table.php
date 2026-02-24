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
        Schema::create('document_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approver_id')->nullable()->constrained('barangay_terms');
            $table->foreignId('on_behalf_of')->nullable()->constrained('barangay_terms'); // If signed for someone else
            $table->foreignId('document_type_id')->constrained('document_type_properties');
            $table->string('signing_capacity')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('pending'); // pending, issued, rejected
            $table->string('request_origin'); // walk-in, online
            $table->foreignId('requester_id')->constrained('users');
            $table->bigInteger('barangay_code'); // point to user's barangay_code (jurisdiction)
            $table->text('purpose')->nullable();
            $table->string('file_path')->nullable();
            $table->char('checksum', 64)->nullable()->unique(); // SHA-256
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_transactions');
    }
};

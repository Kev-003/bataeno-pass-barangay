<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Barangay;
use App\Models\User;
use App\Models\DocumentTypeProperty;
use App\Models\BarangayTerm;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentTransaction>
 */
class DocumentTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'barangay_id' => Barangay::factory(),
            'requester_id' => User::factory(),
            'document_type_id' => DocumentTypeProperty::factory(),
            'status' => 'pending',
            'request_origin' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function issued(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'issued',
            'issued_at' => now(),
            'checksum' => bin2hex(random_bytes(16)),
            'approver_id' => BarangayTerm::factory(),
            'on_behalf_of' => BarangayTerm::factory(),
        ]);
    }
}

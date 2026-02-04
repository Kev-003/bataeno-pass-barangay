<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\House;
use App\Models\Household;
use App\Models\HouseholdMemberProfile;
use App\Models\Barangay;
use App\Models\BarangayTerm;
use App\Models\DocumentTransaction;
use App\Models\DocumentTypeProperty;
use App\Models\DocumentRequirementsDefinition;
use Tests\TestCase;

class DocumentFlowTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_full_document_lifecycle_request_and_sign()
    {
        // --- ARRANGE ---
        $barangay = Barangay::factory()->create();

        // 1. Setup Document Type & Rules
        $docType = DocumentTypeProperty::create([
            'name' => 'Barangay Clearance',
            'code' => 'CLR-001',
            'default_fee' => 100
        ]);

        $requirement = DocumentRequirementsDefinition::create([
            'requirement_name' => 'Valid ID', // Ensure this matches your DB column name
            'data_type' => 'file',
            'description' => 'National ID',
        ]);

        $docType->requirements()->attach($requirement->id);

        // 2. Setup Resident (REAL DATA setup, not mocking)
        $resident = User::factory()->create();

        $house = House::create([
            'barangay_id' => $barangay->id,
            'barangay' => $barangay->name, // Redundant but often required by your schema
            'street' => 'Test St',
            'subdivision' => 'Test Subd',
            'housing_unit' => '1A'
        ]);

        $household = Household::create([
            'house_id' => $house->id,
            'ownership' => 'Owned',
            'monthly_utility_expense' => 0,
            'total_income' => 0
        ]);

        HouseholdMemberProfile::create([
            'user_id' => $resident->id,
            'household_id' => $household->id,
            'membership_type' => 'primary', // Critical for your User logic sorting
            'role' => 'Member',
            'presence_status' => 'Present',
            'started_at' => now(),
        ]);

        // 3. Setup Captain
        $captain = User::factory()->create();
        BarangayTerm::create([
            'user_id' => $captain->id,
            'barangay_id' => $barangay->id,
            'position_type' => 'Captain',
            'started_at' => now()->subMonth(),
            'ended_at' => now()->addYear(),
        ]);

        // --- ACT 1: Resident Requests ---
        $responseRequest = $this->actingAs($resident)
            ->postJson("/api/barangay/{$barangay->id}/documents/request", [
                'document_type_id' => $docType->id,
                'request_origin' => 'web'
            ]);

        // --- ASSERT 1 ---
        $responseRequest->assertStatus(201);

        $transactionId = $responseRequest->json('transaction_id');
        $this->assertNotNull($transactionId, 'Transaction ID should not be null');

        $this->assertDatabaseHas('document_transactions', [
            'id' => $transactionId,
            'status' => 'pending',
            'requester_id' => $resident->id
        ]);

        // --- ACT 2: Captain Signs ---
        $responseSign = $this->actingAs($captain)
            ->patchJson("/api/barangay/{$barangay->id}/documents/{$transactionId}/sign");

        // --- ASSERT 2 ---
        $responseSign->assertStatus(200);

        $transaction = DocumentTransaction::find($transactionId);
        $this->assertEquals('issued', $transaction->status);
        $this->assertEquals($captain->activeTerm->id, $transaction->approver_id);
        $this->assertNotNull($transaction->checksum);
    }
}

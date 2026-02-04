<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Barangay;
use App\Models\BarangayTerm;
use App\Models\DocumentTypeProperty;
use App\Models\User;
use App\Models\DocumentTransaction;

class WalkInDocumentTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    private $barangayA;
    private $officialA;
    private $resident;
    private $docType;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Barangay A and its Official
        $this->barangayA = Barangay::factory()->create();
        $this->officialA = User::factory()->create();
        BarangayTerm::factory()->create([
            'user_id' => $this->officialA->id,
            'barangay_id' => $this->barangayA->id,
            'position_type' => 'Secretary',
        ]);

        // Setup a Resident and a Document Type
        $this->resident = User::factory()->create();
        $this->docType = DocumentTypeProperty::factory()->create();
    }

    /** @test */
    public function official_can_process_walk_in_for_resident()
    {
        // ARRANGE: Setup Barangay and Officials
        $barangay = Barangay::factory()->create();

        // Create Captain (has ultimate authority)
        $captain = User::factory()->create();
        $captainTerm = BarangayTerm::factory()->create([
            'user_id' => $captain->id,
            'barangay_id' => $barangay->id,
            'position_type' => 'Captain',
        ]);

        // Create Secretary (will be delegated authority)
        $secretary = User::factory()->create();
        $secretaryTerm = BarangayTerm::factory()->create([
            'user_id' => $secretary->id,
            'barangay_id' => $barangay->id,
            'position_type' => 'Secretary',
        ]);

        // Setup Resident (the person requesting the document)
        $resident = User::factory()->create();

        // Setup Document Type with Requirements (CRITICAL: must have requirements)
        $docType = DocumentTypeProperty::factory()->create();

        // Create and attach a requirement to satisfy the validation
        $requirement = \App\Models\DocumentRequirementsDefinition::create([
            'requirement_name' => 'Valid ID',
            'data_type' => 'file',
            'description' => 'Government-issued ID'
        ]);
        $docType->requirements()->attach($requirement->id);

        // Create Delegation: Captain delegates signing authority to Secretary
        \App\Models\Delegation::create([
            'granter_term_id' => $captainTerm->id,
            'delegate_term_id' => $secretaryTerm->id,
            'document_type_id' => $docType->id,
            'expires_at' => now()->addYear(),
        ]);

        // ACT 1: Secretary creates request on behalf of resident (walk-in)
        $payload = [
            'document_type_id' => $docType->id,
            'request_origin' => 'walk_in',
            'requester_id' => $resident->id, // Secretary specifies who the document is for
        ];

        $response = $this->actingAs($secretary)
            ->postJson("/api/barangay/{$barangay->id}/documents/request", $payload);

        // ASSERT 1: Request created successfully
        $response->assertStatus(201);
        $transactionId = $response->json('transaction_id');
        $this->assertNotNull($transactionId, 'Transaction ID should not be null');

        $this->assertDatabaseHas('document_transactions', [
            'id' => $transactionId,
            'status' => 'pending',
            'requester_id' => $resident->id, // Document is for the resident
            'document_type_id' => $docType->id,
            'request_origin' => 'walk_in',
            'barangay_id' => $barangay->id,
        ]);

        // ACT 2: Secretary signs the document (using delegated authority)
        $responseSign = $this->actingAs($secretary)
            ->patchJson("/api/barangay/{$barangay->id}/documents/{$transactionId}/sign");

        // ASSERT 2: Document signed and issued
        $responseSign->assertStatus(200);

        $transaction = DocumentTransaction::find($transactionId);
        $this->assertEquals('issued', $transaction->status);
        $this->assertEquals($secretary->activeTerm->id, $transaction->approver_id);
        $this->assertNotNull($transaction->checksum);
        $this->assertNotNull($transaction->issued_at);
    }
}

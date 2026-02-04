<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Barangay;
use App\Models\BarangayTerm;
use App\Models\Delegation;
use App\Models\DocumentTypeProperty;
use App\Models\DocumentTransaction;
use App\Services\GovernanceService;

class GovernanceServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to set up a valid transaction for a specific barangay.
     */
    private function createTransactionForBarangay($barangayId, $docCode = 'BC')
    {
        // Create the "Rulebook" for the document
        $property = DocumentTypeProperty::create([
            'code' => $docCode,
            'name' => 'Barangay Clearance',
            'description' => 'Official clearance document',
            'default_fee' => 50.00,
            'validity_days' => 30
        ]);

        // Create the actual "Request"
        return DocumentTransaction::create([
            'document_type_id' => $property->id, // Fixed: connects to the property
            'barangay_id' => $barangayId,   // Fixed: transaction owns the location
            'status' => 'pending',
            'request_origin' => 'web',
            'requester_id' => User::factory()->create()->id,
            'checksum' => bin2hex(random_bytes(16)),
        ]);
    }

    public function test_captain_has_automatic_authority()
    {
        $barangay = Barangay::factory()->create();
        $user = User::factory()->create();

        BarangayTerm::create([
            'user_id' => $user->id,
            'position_type' => 'Captain',
            'barangay_id' => $barangay->id,
            'started_at' => now()->subYear(),
            'ended_at' => now()->addYear(),
        ]);

        $transaction = $this->createTransactionForBarangay($barangay->id);
        $user->refresh();

        $service = new GovernanceService();
        $this->assertTrue($service->canSign($user, $transaction->id));
    }

    public function test_delegated_official_can_sign()
    {
        $barangay = Barangay::factory()->create();
        $captain = User::factory()->create();
        $secretary = User::factory()->create();

        $granterTerm = BarangayTerm::create([
            'user_id' => $captain->id,
            'position_type' => 'Captain',
            'barangay_id' => $barangay->id,
            'started_at' => now()->subYear(),
            'ended_at' => now()->addYear(),
        ]);

        $delegateTerm = BarangayTerm::create([
            'user_id' => $secretary->id,
            'position_type' => 'Secretary',
            'barangay_id' => $barangay->id,
            'started_at' => now()->subYear(),
            'ended_at' => now()->addYear(),
        ]);

        $transaction = $this->createTransactionForBarangay($barangay->id, 'BC');

        Delegation::create([
            'delegate_term_id' => $delegateTerm->id,
            'document_type_id' => $transaction->document_type_id,
            'granter_term_id' => $granterTerm->id,
            'expires_at' => now()->addMonth(),
        ]);

        $secretary->refresh();
        $service = new GovernanceService();

        // Testing the full integration via canSign
        $this->assertTrue($service->canSign($secretary, $transaction->id));
    }

    public function test_expired_delegation_cannot_sign()
    {
        $barangay = Barangay::factory()->create();
        $captain = User::factory()->create();
        $secretary = User::factory()->create();

        $granterTerm = BarangayTerm::create([
            'user_id' => $captain->id,
            'position_type' => 'Captain',
            'barangay_id' => $barangay->id,
            'started_at' => now()->subYear(),
            'ended_at' => now()->addYear(),
        ]);

        $delegateTerm = BarangayTerm::create([
            'user_id' => $secretary->id,
            'position_type' => 'Secretary',
            'barangay_id' => $barangay->id,
            'started_at' => now()->subYear(),
            'ended_at' => now()->addYear(),
        ]);

        $transaction = $this->createTransactionForBarangay($barangay->id, 'BC');

        Delegation::create([
            'delegate_term_id' => $delegateTerm->id,
            'document_type_id' => $transaction->document_type_id,
            'granter_term_id' => $granterTerm->id,
            'expires_at' => now()->subDay(),
        ]);

        $secretary->refresh();
        $service = new GovernanceService();

        $this->assertFalse($service->canSign($secretary, $transaction->id));
    }

    public function test_captain_term_expired()
    {
        $barangay = Barangay::factory()->create();
        $user = User::factory()->create();

        BarangayTerm::create([
            'user_id' => $user->id,
            'position_type' => 'Captain',
            'barangay_id' => $barangay->id,
            'started_at' => now()->subYear(),
            'ended_at' => now()->subDay(), // Expired
        ]);

        $transaction = $this->createTransactionForBarangay($barangay->id);
        $user->refresh();

        $service = new GovernanceService();
        $this->assertFalse($service->canSign($user, $transaction->id));
    }

    public function test_official_signing_for_different_barangay()
    {
        $barangayA = Barangay::factory()->create();
        $barangayB = Barangay::factory()->create();

        $user = User::factory()->create();
        BarangayTerm::create([
            'user_id' => $user->id,
            'position_type' => 'Captain',
            'barangay_id' => $barangayA->id,
            'started_at' => now()->subYear(),
            'ended_at' => now()->addYear(),
        ]);

        // Transaction belongs to Barangay B
        $transaction = $this->createTransactionForBarangay($barangayB->id);
        $user->refresh();

        $service = new GovernanceService();

        // Captain of A cannot sign Transaction of B
        $this->assertFalse($service->canSign($user, $transaction->id));
    }
}
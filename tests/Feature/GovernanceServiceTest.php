<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\BarangayTerm;
use App\Models\Delegation;
use App\Services\GovernanceService;
class GovernanceServiceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_captain_has_automatic_authority()
    {
        $barangay = \App\Models\Barangay::factory()->create();
        // 1. Arrange: Create a Captain
        $user = User::factory()->create();
        BarangayTerm::create([
            'user_id' => $user->id,
            'position_type' => 'Captain',
            'barangay_id' => $barangay->id,
            'started_at' => now()->subYear(),
            'ended_at' => now()->addYear(),
        ]);

        // 2. Act: Call the Service
        $service = new GovernanceService();
        $canSign = $service->canSign($user, 1);

        // 3. Assert: Expect true
        $this->assertTrue($canSign);
    }

    public function test_delegated_official_can_sign()
    {
        $barangay = \App\Models\Barangay::factory()->create();

        // 1. Create the Granter (Captain) - required by foreign key
        $captain = User::factory()->create();
        $granterTerm = BarangayTerm::create([
            'user_id' => $captain->id,
            'position_type' => 'Captain',
            'barangay_id' => $barangay->id,
            'started_at' => now()->subYear(),
            'ended_at' => now()->addYear(),
        ]);

        // 2. Create the Delegate (Secretary)
        $secretary = User::factory()->create();
        $delegateTerm = BarangayTerm::create([
            'user_id' => $secretary->id,
            'position_type' => 'Secretary',
            'barangay_id' => $barangay->id,
            'started_at' => now()->subYear(),
            'ended_at' => now()->addYear(),
        ]);

        // 3. Create the Delegation with valid Foreign Keys
        Delegation::create([
            'delegate_term_id' => $delegateTerm->id,
            'document_type_id' => 1,
            'granter_term_id' => $granterTerm->id, // Real ID, not 999
            'expires_at' => now()->addMonth(),
        ]);

        $service = new GovernanceService();
        $canSign = $service->isDelegated($secretary, 1);

        $this->assertTrue($canSign);
    }

    public function test_expired_delegation_cannot_sign()
    {
        $barangay = \App\Models\Barangay::factory()->create();

        // 1. Arrange: Create Document Type
        // Note: Using 1 to match your updated test IDs
        // \App\Models\DocumentTypeProperty::firstOrCreate([
        //     'id' => 1,
        //     'name' => 'Barangay Clearance',
        //     'code' => 'BC'
        // ]);

        $captain = User::factory()->create();
        $granterTerm = BarangayTerm::create([
            'user_id' => $captain->id,
            'position_type' => 'Captain',
            'barangay_id' => $barangay->id,
            'started_at' => now()->subYear(),
            'ended_at' => now()->addYear(),
        ]);

        // 2. Create Secretary with an ACTIVE term
        $secretary = User::factory()->create();
        $delegateTerm = BarangayTerm::create([
            'user_id' => $secretary->id,
            'position_type' => 'Secretary',
            'barangay_id' => $barangay->id,
            'started_at' => now()->subYear(),
            'ended_at' => now()->addYear(),
        ]);

        // 3. Create an EXPIRED delegation
        Delegation::create([
            'delegate_term_id' => $delegateTerm->id,
            'document_type_id' => 1,
            'granter_term_id' => $granterTerm->id,
            'expires_at' => now()->subDay(), // Expired yesterday
        ]);

        $service = new GovernanceService();

        // 4. Act: Check delegation
        $canSign = $service->isDelegated($secretary, 1);

        // 5. Assert: Should be false because the delegation specifically has expired
        $this->assertFalse($canSign);
    }

    public function test_captain_term_expired()
    {
        $barangay = \App\Models\Barangay::factory()->create();
        // 1. Arrange: Create a Captain
        $user = User::factory()->create();
        BarangayTerm::create([
            'user_id' => $user->id,
            'position_type' => 'Captain',
            'barangay_id' => $barangay->id,
            'started_at' => now()->subYear(),
            'ended_at' < now()->subDay(),
        ]);

        // 2. Act: Call the Service
        $service = new GovernanceService();
        $canSign = $service->canSign($user, 1);

        // 3. Assert: Expect false
        $this->assertFalse($canSign);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\House;
use App\Models\Barangay;
use App\Models\Household;
use App\Models\BarangayTerm;
use App\Models\HouseholdMemberProfile;
use Illuminate\Support\Facades\Route;

class BarangayAccessMiddlewareTest extends TestCase
{
    /**
     * Test barangay-level access controls.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Define a temporary route to test the middleware
        Route::middleware(['web', \App\Http\Middleware\EnsureUserBelongsToBarangay::class])
            ->get('/test-barangay/{barangay_id}', function () {
                return 'Access Granted';
            });
    }

    public function test_resident_with_valid_crawl_can_access_their_barangay()
    {
        $barangay = Barangay::factory()->create();

        $house = House::create([
            'barangay_id' => $barangay->id,
            'housing_unit' => 'Unit A',
            'street' => 'Main St',
            'subdivision' => 'Phase 1',
            'barangay' => $barangay->name
        ]);

        $household = Household::create([
            'house_id' => $house->id,
            'ownership' => 'Owned',
            'monthly_utility_expense' => 1500.00,
            'total_income' => 50000.00,
        ]);

        $user = User::factory()->create();

        HouseholdMemberProfile::create([
            'user_id' => $user->id,
            'household_id' => $household->id,
            'role' => 'Member',
            'membership_type' => 'Resident',
            'presence_status' => 'Present',
            'started_at' => now()->subDays(10),
            'ended_at' => null, // Explicitly set to null to match our "Active" logic
        ]);

        // REFRESH IS KEY: This loads the profile into the $user object
        $user->refresh();

        $response = $this->actingAs($user)->get("/test-barangay/{$barangay->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_access_different_barangay()
    {
        $barangayA = Barangay::factory()->create();
        $barangayB = Barangay::factory()->create();

        $house = House::create([
            'barangay_id' => $barangayA->id,
            'barangay' => $barangayA->name,
            'street' => 'Street A',      // Added missing required field
            'subdivision' => 'Subd A',   // Added missing required field
            'housing_unit' => '101'
        ]);

        $household = Household::create([
            'house_id' => $house->id,
            'ownership' => 'Owned',
            'monthly_utility_expense' => 1500.00,
            'total_income' => 50000.00,
        ]);
        $user = User::factory()->create();

        HouseholdMemberProfile::create([
            'user_id' => $user->id,
            'household_id' => $household->id,
            'role' => 'Member',
            'membership_type' => 'Resident',
            'presence_status' => 'Present',
            'started_at' => now()->subDays(10),
        ]);

        $response = $this->actingAs($user)->get("/test-barangay/{$barangayB->id}");

        $response->assertStatus(403);
    }

    public function test_official_can_access_their_assigned_term_barangay()
    {
        // Arrange
        $barangay = Barangay::factory()->create();
        $user = User::factory()->create();

        BarangayTerm::create([
            'user_id' => $user->id,
            'barangay_id' => $barangay->id,
            'position_type' => 'Captain',
            'started_at' => now()->subYear(),
            'ended_at' => now()->addYear(),
        ]);

        // Act & Assert using the generated ID
        $this->actingAs($user)
            ->get("/test-barangay/{$barangay->id}")
            ->assertStatus(200);
    }

    public function test_official_cannot_access_different_barangay()
    {
        // Arrange
        $barangayA = Barangay::factory()->create();
        $barangayB = Barangay::factory()->create();
        $user = User::factory()->create();

        BarangayTerm::create([
            'user_id' => $user->id,
            'barangay_id' => $barangayB->id,
            'position_type' => 'Captain',
            'started_at' => now()->subYear(),
            'ended_at' => now()->addYear(),
        ]);

        // Act & Assert using the generated ID
        $this->actingAs($user)
            ->get("/test-barangay/{$barangayA->id}")
            ->assertStatus(403);
    }
}

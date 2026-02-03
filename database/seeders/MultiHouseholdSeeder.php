<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Municipality;
use App\Models\Barangay;
use App\Models\House;
use App\Models\Household;
use App\Models\Family;
use App\Models\User;
use App\Models\HouseholdMemberProfile;

class MultiHouseholdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get a location (Balanga)
        $municipality = Municipality::where('name', 'Balanga City')->first();
        if (!$municipality) {
            // Fallback if seeded data missing
            return;
        }
        $barangay = $municipality->barangays->first();

        // 2. Create ONE House (Shared compound/building)
        $sharedHouse = House::factory()->create([
            'barangay_id' => $barangay->id,
            'barangay' => $barangay->name,
            'street' => 'Crowded Street',
            'housing_unit' => 'Compound A'
        ]);

        $this->command->info("Created Shared House ID: {$sharedHouse->id} at {$sharedHouse->street}");

        // 3. Create 2 Households in this house
        for ($i = 1; $i <= 2; $i++) {
            // Create a Family for this household
            $family = Family::factory()->create([
                'family_name' => "MultiFam $i"
            ]);

            // Create the Household entity
            $household = Household::factory()->create([
                'house_id' => $sharedHouse->id,
                'ownership' => ($i == 1) ? 'Owned' : 'Rented' // First one owns, second rents
            ]);

            // Create Head of Household
            $head = User::factory()->create([
                'first_name' => "Head$i",
                'last_name' => $family->family_name,
                'family_id' => $family->id
            ]);

            $headProfile = HouseholdMemberProfile::factory()->create([
                'user_id' => $head->id,
                'household_id' => $household->id,
                'role' => 'Head',
                'presence_status' => 'Present'
            ]);
            $household->update(['household_head_id' => $headProfile->id]);

            // Create 1 Member
            $member = User::factory()->create([
                'first_name' => "Member$i",
                'last_name' => $family->family_name,
                'family_id' => $family->id
            ]);

            HouseholdMemberProfile::factory()->create([
                'user_id' => $member->id,
                'household_id' => $household->id,
                'role' => 'Member',
                'presence_status' => 'Present'
            ]);
        }
    }
}

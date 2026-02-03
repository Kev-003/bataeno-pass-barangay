<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Family;
use App\Models\Household;
use App\Models\HouseholdMemberProfile;
use App\Models\Municipality;
use App\Models\Barangay;
use App\Models\House;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed locations (BataanSeeder creates Municipalities and Barangays)
        $this->call(BataanSeeder::class);

        // 2. Create Families
        $families = Family::factory(5)->create();

        // 3. Create Users linked to Families
        User::factory(10)->create([
            'family_id' => $families->random()->id
        ]);

        // 4. Create Households and Members
        $households = Household::factory(5)->create();

        $households->each(function ($household) use ($families) {
            // Create a head
            $headUser = User::factory()->create(['family_id' => $families->random()->id]);
            $headProfile = HouseholdMemberProfile::factory()->create([
                'user_id' => $headUser->id,
                'household_id' => $household->id,
                'role' => 'Head'
            ]);

            $household->update(['household_head_id' => $headProfile->id]);

            // Create members
            HouseholdMemberProfile::factory(2)->create([
                'household_id' => $household->id,
                'role' => 'Member'
            ]);
        });

        // 5. Create Specific Scenario: Student moving to another municipality
        $this->createStudentScenario();

        // 6. Create Multi-Household Scenario
        $this->call(MultiHouseholdSeeder::class);

        // 7. Create Legal Document Transaction Scenario
        $this->call(DocumentTransactionSeeder::class);
    }

    private function createStudentScenario()
    {
        // Find two different municipalities (assuming BataanSeeder created them)
        $municipalities = Municipality::with('barangays')->take(2)->get();
        if ($municipalities->count() < 2) {
            // Fallback if not enough data
            return;
        }

        $homeMuni = $municipalities[0];
        $schoolMuni = $municipalities[1];

        $homeBarangay = $homeMuni->barangays->first();
        $schoolBarangay = $schoolMuni->barangays->first();

        // 1. Create the Student User
        $studentFamily = Family::factory()->create(['family_name' => 'Santos']); // Valid family
        $student = User::factory()->create([
            'first_name' => 'Kev', // Identifiable name
            'last_name' => 'Santos',
            'family_id' => $studentFamily->id,
            'occupation' => 'Student',
            'date_of_birth' => '2004-01-01', // Approx 22 yrs old
        ]);

        // 2. Create Home Household (Dependent Status)
        $homeHouse = House::factory()->create([
            'barangay_id' => $homeBarangay->id,
            'barangay' => $homeBarangay->name,
            'street' => 'Home Street'
        ]);

        $homeHousehold = Household::factory()->create([
            'house_id' => $homeHouse->id,
            'ownership' => 'Owned',
        ]);

        // Assign a head to the home household (e.g., Father)
        $father = User::factory()->create([
            'first_name' => 'Dad',
            'last_name' => 'Santos',
            'family_id' => $studentFamily->id,
        ]);

        $fatherProfile = HouseholdMemberProfile::factory()->create([
            'user_id' => $father->id,
            'household_id' => $homeHousehold->id,
            'role' => 'Head',
            'presence_status' => 'Present',
        ]);
        $homeHousehold->update(['household_head_id' => $fatherProfile->id]);

        // Add Student as Dependent (Away)
        HouseholdMemberProfile::factory()->create([
            'user_id' => $student->id,
            'household_id' => $homeHousehold->id,
            'role' => 'Dependent',
            'presence_status' => 'Away for Study', // Not Present
            'membership_type' => 'Resident',
        ]);


        // 3. Create Student Household (Head Status) in new Municipality
        $schoolHouse = House::factory()->create([
            'barangay_id' => $schoolBarangay->id,
            'barangay' => $schoolBarangay->name,
            'street' => 'Dorm Street'
        ]);

        $schoolHousehold = Household::factory()->create([
            'house_id' => $schoolHouse->id,
            'ownership' => 'Rented',
        ]);

        // Add Student as Head (Present)
        $studentSchoolProfile = HouseholdMemberProfile::factory()->create([
            'user_id' => $student->id,
            'household_id' => $schoolHousehold->id,
            'role' => 'Head',
            'presence_status' => 'Present', // Active here
            'membership_type' => 'Transient', // Maybe transient since studying? or Resident if they registered. defaulting to Resident/Transient logic
        ]);

        $schoolHousehold->update(['household_head_id' => $studentSchoolProfile->id]);
    }
}

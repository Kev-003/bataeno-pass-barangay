<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Municipality;
use App\Models\Barangay;
use App\Models\Family;
use App\Models\House;
use App\Models\Household;
use App\Models\HouseholdMemberProfile;
use App\Models\DocumentTransaction;

class TestRelationships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:relationships';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test model relationships on seeded data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Relationship Tests...');

        // 1. Municipality <-> Barangay
        $this->info('Testing Municipality <-> Barangay:');
        $municipality = Municipality::first();
        if ($municipality) {
            $this->comment("Municipality found: {$municipality->name}");
            $barangays = $municipality->barangays;
            $this->line(" - Count of Barangays: " . $barangays->count());
        } else {
            $this->error('No Municipality found.');
        }

        $barangay = Barangay::first();
        if ($barangay) {
            $this->comment("Barangay found: {$barangay->name}");
            $muni = $barangay->municipality;
            $this->line(" - Belongs to Municipality: " . ($muni ? $muni->name : 'None'));
        } else {
            $this->error('No Barangay found.');
        }

        // 2. User <-> Family
        $this->info("\nTesting User <-> Family:");
        $user = User::whereNotNull('family_id')->first();
        if ($user) {
            $this->comment("User with Family found: {$user->first_name}");
            $family = $user->family;
            $this->line(" - Belongs to Family: " . ($family ? $family->family_name : 'None'));
        } else {
            $this->warn('No User with family_id found (might be nullable or not seeded).');
        }

        // 3. User <-> Barangay (via BarangayTerm)
        $this->info("\nTesting User <-> Barangay (via BarangayTerm):");
        $term = \App\Models\BarangayTerm::first();
        if ($term) {
            $this->comment("BarangayTerm found: ID {$term->id}");
            $u = $term->user;
            $b = $term->barangay;
            $this->line(" - Linked User: " . ($u ? $u->first_name : 'None'));
            $this->line(" - Linked Barangay: " . ($b ? $b->name : 'None'));
        } else {
            $this->warn('No BarangayTerm found.');
        }

        // 4. House <-> Barangay
        $this->info("\nTesting House <-> Barangay:");
        $house = House::first();
        if ($house) {
            $this->comment("House found: ID {$house->id}");
            $brgy = $house->linkedBarangay;
            $this->line(" - Located in Barangay (Relation): " . ($brgy ? $brgy->name : 'None'));
            $households = $house->households;
            $this->line(" - Households count: " . $households->count());
        } else {
            $this->warn('No House found.');
        }

        // 4. Household <-> Members
        $this->info("\nTesting Household <-> Members:");
        $household = Household::first();
        if ($household) {
            $this->comment("Household found: ID {$household->id}");
            $houseRef = $household->house;
            $this->line(" - Linked to House ID: " . ($houseRef ? $houseRef->id : 'None'));
            $members = $household->members;
            $this->line(" - Member Profiles count: " . $members->count());
        } else {
            $this->warn('No Household found.');
        }

        $this->info("\nRelationship tests completed.");

        // 5. Verify Student Scenario
        $this->info("\nTesting Student Scenario:");
        $student = User::where('first_name', 'Kev')->where('last_name', 'Santos')->first();
        if ($student) {
            $this->comment("Student found: {$student->first_name} {$student->last_name}");

            // Get profiles
            $profiles = HouseholdMemberProfile::where('user_id', $student->id)->orderBy('id')->get();
            $this->line(" - Total Household Profiles: " . $profiles->count());

            foreach ($profiles as $p) {
                $h = $p->household;
                $house = $h ? $h->house : null;
                $brgy = $house ? $house->linkedBarangay : null;
                $muni = $brgy ? $brgy->municipality : null;

                $this->line("   > Profile ID {$p->id}:");
                $this->line("     - Role: {$p->role}");
                $this->line("     - Status: {$p->presence_status}");
                $this->line("     - Location: " . ($brgy ? $brgy->name : 'N/A') . ", " . ($muni ? $muni->name : 'N/A'));
            }

        } else {
            $this->warn("Student 'Kev Santos' not found. Did you re-run db:seed?");
        }

        // 6. Verify Multi-Household Scenario
        $this->info("\nTesting Multi-Household Scenario:");
        // Search for the specific house created in MultiHouseholdSeeder
        $multiHouse = House::where('street', 'Crowded Street')->first();
        if ($multiHouse) {
            $this->comment("Shared House found: ID {$multiHouse->id} at {$multiHouse->street}");
            $households = $multiHouse->households;
            $this->line(" - Households in this house: " . $households->count());

            foreach ($households as $idx => $hh) {
                $headProfile = $hh->headOfHousehold;
                $headUser = $headProfile ? $headProfile->user : null;
                $family = $headUser ? $headUser->family : null;

                $this->line("   > Household #{$idx} (ID {$hh->id}):");
                $this->line("     - Ownership: {$hh->ownership}");
                $this->line("     - Head: " . ($headUser ? $headUser->first_name : 'N/A'));
                $this->line("     - Family: " . ($family ? $family->family_name : 'N/A'));
                $this->line("     - Total Members: " . $hh->members->count());
            }

        } else {
            $this->warn("House at 'Crowded Street' not found. Did you re-run db:seed?");
        }
    }
}

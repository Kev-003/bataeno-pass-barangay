<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Family;
use App\Models\Household;
use App\Models\HouseholdMemberProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LineageSeeder extends Seeder
{
    /**
     * Assigns family_id, mother_id, father_id to residents.
     * Creates generational structure using NUCLEAR family units.
     * Soft-deletes some ancestors as "deceased".
     */
    public function run(): void
    {
        $santoDomingo = \App\Models\Barangay::where('name', 'Santo Domingo')->first();
        if (!$santoDomingo) {
            $this->command->error('Santo Domingo not found.');
            return;
        }

        $families = Family::where('barangay_id', $santoDomingo->id)->get();
        if ($families->isEmpty()) {
            $this->command->error('No families found. Run FamilySeeder first.');
            return;
        }

        $residents = User::where('barangay_id', $santoDomingo->id)
            ->whereNull('family_id')
            ->get()
            ->shuffle();

        if ($residents->isEmpty()) {
            $this->command->error('No unassigned residents found. Run ResidentSeeder first.');
            return;
        }

        $residentPool = $residents->values();
        $poolIndex = 0;
        $createdParents = 0;
        $createdGrandparents = 0;
        $deceasedCount = 0;

        foreach ($families as $family) {
            // --- GENERATION 2: Select 2-4 children ---
            $numChildren = min(fake()->numberBetween(2, 4), $residentPool->count() - $poolIndex);
            if ($numChildren <= 0)
                continue;

            $children = [];
            for ($c = 0; $c < $numChildren; $c++) {
                if ($poolIndex >= $residentPool->count())
                    break;
                $child = $residentPool[$poolIndex];
                $poolIndex++;
                $children[] = $child;
            }

            if (empty($children))
                continue;

            // --- GENERATION 1: Create Father and Mother for this Family ---
            $father = $this->createAncestor(
                $santoDomingo,
                'Male',
                'Married',
                fake()->dateTimeBetween('-65 years', '-40 years')->format('Y-m-d'),
                $family->family_name
            );
            $mother = $this->createAncestor(
                $santoDomingo,
                'Female',
                'Married',
                fake()->dateTimeBetween('-60 years', '-38 years')->format('Y-m-d'),
                fake()->lastName()
            );

            // Set them as the core parents of this family unit
            $family->update([
                'father_id' => $father->id,
                'mother_id' => $mother->id,
            ]);

            // Assign everyone to this nuclear family
            foreach (array_merge([$father, $mother], $children) as $member) {
                $member->update(['family_id' => $family->id]);
            }

            // Link children to parents
            foreach ($children as $child) {
                $child->update([
                    'father_id' => $father->id,
                    'mother_id' => $mother->id,
                ]);
            }

            // Household profiles
            $this->assignToHousehold($family, $father, 'Head');
            $this->assignToHousehold($family, $mother, 'Spouse');
            foreach ($children as $child) {
                $this->assignToHousehold($family, $child, 'Dependent');
            }

            // --- GENERATION 0: Create Grandparents (separate family unit) ---
            if (fake()->boolean(60)) {
                // Create a separate family for grandparents to avoid bloating
                $grandFamily = Family::create([
                    'family_name' => $father->last_name . " (Ancestors)",
                    'barangay_id' => $santoDomingo->id,
                ]);

                $grandfather = $this->createAncestor(
                    $santoDomingo,
                    'Male',
                    'Married',
                    fake()->dateTimeBetween('-95 years', '-65 years')->format('Y-m-d'),
                    $father->last_name
                );
                $grandmother = $this->createAncestor(
                    $santoDomingo,
                    'Female',
                    'Married',
                    fake()->dateTimeBetween('-90 years', '-63 years')->format('Y-m-d'),
                    fake()->lastName()
                );

                $grandFamily->update([
                    'father_id' => $grandfather->id,
                    'mother_id' => $grandmother->id,
                ]);

                $grandfather->update(['family_id' => $grandFamily->id]);
                $grandmother->update(['family_id' => $grandFamily->id]);

                // Link father to grandparents
                $father->update([
                    'father_id' => $grandfather->id,
                    'mother_id' => $grandmother->id,
                ]);

                // Soft-delete deceased grandparents
                if (fake()->boolean(70)) {
                    $grandfather->delete();
                    $deceasedCount++;
                }
                if (fake()->boolean(50)) {
                    $grandmother->delete();
                    $deceasedCount++;
                }
            }
        }

        // Final Cleanup: Remove any families created by FamilySeeder that were not used/populated
        $emptyFamilies = Family::whereDoesntHave('members')->get();
        foreach ($emptyFamilies as $f) {
            $f->delete();
        }

        $this->command->info("✅ Lineage complete (Nuclear Units)");
        $this->command->info("   - Removed " . $emptyFamilies->count() . " unused/ghost families.");
    }

    private function createAncestor($barangay, $gender, $civilStatus, $dob, $lastName): User
    {
        return User::create([
            'uuid' => Str::uuid(),
            'first_name' => $gender === 'Male' ? fake()->firstNameMale() : fake()->firstNameFemale(),
            'last_name' => $lastName,
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'date_of_birth' => $dob,
            'gender' => $gender,
            'civil_status' => $civilStatus,
            'barangay_id' => $barangay->id,
            'municity_id' => $barangay->municity_code,
        ]);
    }

    private function assignToHousehold(\App\Models\Family $family, \App\Models\User $user, string $role): void
    {
        if (!$family->household_id)
            return;
        HouseholdMemberProfile::create([
            'user_id' => $user->id,
            'household_id' => $family->household_id,
            'role' => $role,
            'membership_type' => 'primary',
            'presence_status' => $user->trashed() ? 'Deceased' : 'Present',
            'started_at' => now(),
        ]);
    }
}

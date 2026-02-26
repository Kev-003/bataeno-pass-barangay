<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Family;
use App\Models\Household;

class FamilySeeder extends Seeder
{
    public function run(): void
    {
        $santoDomingo = DB::table('barangays')->where('name', 'Santo Domingo')->first();
        if (!$santoDomingo) {
            $this->command->error('Santo Domingo not found. Run BarangaySeeder first.');
            return;
        }

        // Get all households in Santo Domingo (via houses)
        $households = Household::whereHas('house', function ($q) use ($santoDomingo) {
            $q->where('barangay_id', $santoDomingo->id);
        })->get();

        if ($households->isEmpty()) {
            $this->command->error('No households found. Run HouseholdSeeder first.');
            return;
        }

        $this->command->info("Creating families for {$households->count()} households...");

        // Common Filipino surnames for variety
        $surnames = [
            'Santos',
            'Reyes',
            'Cruz',
            'Bautista',
            'Del Rosario',
            'Gonzales',
            'Garcia',
            'Mendoza',
            'Torres',
            'Villanueva',
            'Ramos',
            'Aquino',
            'Fernandez',
            'Castillo',
            'Rivera',
            'Navarro',
            'Morales',
            'Pascual',
            'Salvador',
            'De Leon',
            'Manalo',
            'Soriano',
            'Mercado',
            'Dizon',
            'Tolentino',
        ];

        $totalFamilies = 0;

        foreach ($households as $household) {
            // 1-2 families per household (weighted: ~70% single, ~30% two)
            $numFamilies = fake()->randomElement([1, 1, 1, 1, 1, 1, 1, 2, 2, 2]);

            for ($i = 0; $i < $numFamilies; $i++) {
                Family::create([
                    'family_name' => $surnames[array_rand($surnames)],
                    'household_id' => $household->id,
                    'barangay_id' => $santoDomingo->id,
                ]);
                $totalFamilies++;
            }
        }

        $this->command->info("✅ {$totalFamilies} families created across {$households->count()} households.");
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\House;
use App\Models\Household;

class HouseholdSeeder extends Seeder
{
    public function run(): void
    {
        $santoDomingo = DB::table('barangays')->where('name', 'Santo Domingo')->first();
        if (!$santoDomingo) {
            $this->command->error('Santo Domingo not found. Run BarangaySeeder first.');
            return;
        }

        $houses = House::where('barangay_id', $santoDomingo->id)->get();

        if ($houses->isEmpty()) {
            $this->command->error('No houses found. Run HouseSeeder first.');
            return;
        }

        $this->command->info('Creating households for ' . $houses->count() . ' houses...');

        $totalHouseholds = 0;

        foreach ($houses as $house) {
            // Each house gets 1-3 households (weighted: ~60% single, ~30% two, ~10% three)
            $numHouseholds = fake()->randomElement([1, 1, 1, 1, 1, 1, 2, 2, 2, 3]);

            for ($i = 0; $i < $numHouseholds; $i++) {
                Household::create([
                    'house_id' => $house->id,
                    'ownership' => $i === 0
                        ? fake()->randomElement(['Owned', 'Owned', 'Owned', 'Rented'])
                        : fake()->randomElement(['Rented', 'Living with Relatives']),
                    'monthly_utility_expense' => fake()->randomFloat(2, 800, 5000),
                    'total_income' => fake()->randomFloat(2, 8000, 60000),
                ]);
                $totalHouseholds++;
            }
        }

        $this->command->info("✅ {$totalHouseholds} households created across {$houses->count()} houses.");
    }
}

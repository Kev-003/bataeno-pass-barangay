<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResidentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Target Santo Domingo, Orion
        $santoDomingo = DB::table('barangays')->where('name', 'Santo Domingo')->first();
        if (!$santoDomingo) {
            $this->command->error('Santo Domingo not found. Run BarangaySeeder first.');
            return;
        }

        $orion = DB::table('municipalities')->where('id', $santoDomingo->municity_code)->first();

        $this->command->info('Creating 50 residents for Santo Domingo...');
        for ($i = 0; $i < 50; $i++) {
            User::create([
                'uuid' => Str::uuid(),
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => fake()->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'date_of_birth' => fake()->date('Y-m-d', '-18 years'), // Adult residents
                'place_of_birth' => 'Orion, Bataan',
                'gender' => fake()->randomElement(['Male', 'Female']),
                'civil_status' => fake()->randomElement(['Single', 'Married', 'Widowed', 'Separated']),
                'barangay_code' => $santoDomingo->barangay_code,
                'municity_code' => $orion->municity_code ?? '0000',
                'barangay_name' => $santoDomingo->name,
                'municity_name' => $orion->name,
            ]);
        }

        // 2. Target another barangay (e.g., Bilolo)
        $otherBarangay = DB::table('barangays')->where('name', 'Bilolo')->first();
        if ($otherBarangay) {
            $otherMunicipality = DB::table('municipalities')->where('id', $otherBarangay->municity_code)->first();
            $this->command->info("Creating 5 residents for {$otherBarangay->name}...");
            for ($i = 0; $i < 5; $i++) {
                User::create([
                    'uuid' => Str::uuid(),
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->unique()->safeEmail(),
                    'password' => Hash::make('password'),
                    'date_of_birth' => fake()->date('Y-m-d', '-18 years'),
                    'place_of_birth' => "{$otherMunicipality->name}, Bataan",
                    'gender' => fake()->randomElement(['Male', 'Female']),
                    'civil_status' => 'Single',
                    'barangay_code' => $otherBarangay->barangay_code,
                    'municity_code' => $otherMunicipality->municity_code ?? '0000',
                    'barangay_name' => $otherBarangay->name,
                    'municity_name' => $otherMunicipality->name,
                ]);
            }
        }

        $this->command->info('Resident seeding completed.');
    }
}

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
                'barangay_id' => $santoDomingo->id,
                'municity_id' => $santoDomingo->municity_code,
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
                    'barangay_id' => $otherBarangay->id,
                    'municity_id' => $otherBarangay->municity_code,
                ]);
            }
        }

        // Add Princess Mary Bitua Amianit (Pilar, Kaparangan)
        $pilar = DB::table('municipalities')->where('name', 'Pilar')->first();
        $kaparangan = DB::table('barangays')->where('name', 'Kaparangan')->where('municity_code', $pilar ? $pilar->id : null)->first();
        if ($pilar && $kaparangan) {
            User::create([
                'uuid' => '8421ece2-a06b-45da-9f74-cbf9affa3f90',
                'first_name' => 'PRINCESS MARY',
                'middle_name' => 'BITUA',
                'last_name' => 'AMIANIT',
                'date_of_birth' => '2010-12-02',
                'place_of_birth' => '-',
                'gender' => 'FEMALE',
                'civil_status' => 'SINGLE',
                'barangay_id' => $kaparangan->id,
                'municity_id' => $pilar->id,
                'email' => 'princessmarybituaamianit_20101202@1bataan.gov.ph',
                'password' => '$2y$12$vFfrB9YgTmJF999S/FAhieaAbcUYNjF55w7QDGXfFm6...', // Use a real hash or Hash::make
                'email_verified_at' => '2026-02-27 02:37:07',
                'created_at' => '2026-02-27 02:37:07',
                'updated_at' => '2026-02-27 02:37:07',
            ]);
            $this->command->info('Added Princess Mary Bitua Amianit to residents.');
        } else {
            $this->command->warn('Could not add Princess Mary Bitua Amianit: Pilar/Kaparangan not found.');
        }

        $this->command->info('Resident seeding completed.');
    }
}

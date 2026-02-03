<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Municipality;
use App\Models\Barangay;
use App\Models\User;

class BataanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Balanga City (District 2)
        $balanga = Municipality::firstOrCreate([
            'name' => 'Balanga City',
            'district' => 2,
            'zip_code' => '2100'
        ]);

        // 2. Create Mariveles (District 3)
        $mariveles = Municipality::firstOrCreate([
            'name' => 'Mariveles',
            'district' => 3,
            'zip_code' => '2105'
        ]);

        // 3. Create Specific Barangays for Balanga
        $balangaBarangays = ['San Jose', 'Puerto Rivas Ibaba'];
        foreach ($balangaBarangays as $name) {
            $barangay = Barangay::firstOrCreate([
                'municipality_id' => $balanga->id,
                'name' => $name
            ]);

            // Create 10 Residents per Barangay
            $users = User::factory()->count(10)->create();
            foreach ($users as $user) {
                \App\Models\BarangayTerm::create([
                    'barangay_id' => $barangay->id,
                    'user_id' => $user->id,
                    'position_type' => 'Resident',
                    'started_at' => now(),
                ]);
            }
        }

        // 4. Create Specific Barangays for Mariveles
        $marivelesBarangays = ['Alas-asin', 'Cabcaben'];
        foreach ($marivelesBarangays as $name) {
            $barangay = Barangay::firstOrCreate([
                'municipality_id' => $mariveles->id,
                'name' => $name
            ]);

            // Create 10 Residents per Barangay
            $users = User::factory()->count(10)->create();
            foreach ($users as $user) {
                \App\Models\BarangayTerm::create([
                    'barangay_id' => $barangay->id,
                    'user_id' => $user->id,
                    'position_type' => 'Resident',
                    'started_at' => now(),
                ]);
            }
        }
    }
}

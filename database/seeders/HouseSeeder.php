<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\House;

class HouseSeeder extends Seeder
{
    public function run(): void
    {
        $santoDomingo = DB::table('barangays')->where('name', 'Santo Domingo')->first();
        if (!$santoDomingo) {
            $this->command->error('Santo Domingo not found. Run BarangaySeeder first.');
            return;
        }

        // Fictional streets and subdivisions for Santo Domingo, Orion, Bataan
        $streets = [
            // Main streets
            'Rizal Street',
            'Mabini Street',
            'Bonifacio Street',
            'Aguinaldo Street',
            'Quezon Avenue',
            'Luna Street',
            'Del Pilar Street',
            'Jacinto Street',
            'Lakandula Street',
            'Lapu-Lapu Street',
            // Sitio-style streets
            'Sitio Malinis',
            'Sitio Mabuhay',
            'Sitio Bagong Silang',
            'Sitio Pag-asa',
        ];

        $subdivisions = [
            null,                        // No subdivision
            null,
            null,
            'Villa Esperanza',
            'Santo Domingo Heights',
            'Sunrise Village',
            'Golden Acres',
            null,
        ];

        $this->command->info('Creating 20 houses for Santo Domingo...');

        for ($i = 0; $i < 20; $i++) {
            House::create([
                'barangay_id' => $santoDomingo->id,
                'housing_unit' => fake()->optional(0.3)->numerify('Unit ##'),
                'street' => $streets[array_rand($streets)],
                'subdivision' => $subdivisions[array_rand($subdivisions)],
                'barangay' => 'Santo Domingo',
            ]);
        }

        $this->command->info('✅ 20 houses created for Santo Domingo.');
    }
}

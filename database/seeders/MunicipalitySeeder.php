<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MunicipalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Fetching Bataan Data from psgc.gitlab.io...');

        // 1. FETCH MUNICIPALITIES (Abucay, Bagac, etc.)
        $munis = Http::withoutVerifying()
            ->get('https://psgc.gitlab.io/api/provinces/030800000/municipalities/')
            ->json();

        // 2. FETCH CITIES (Balanga City)
        // We need this because Balanga is a Component City, not a Municipality
        $cities = Http::withoutVerifying()
            ->get('https://psgc.gitlab.io/api/provinces/030800000/cities/')
            ->json();

        // 3. MERGE THEM
        // If either request failed (returned null), use an empty array
        $all = array_merge($munis ?? [], $cities ?? []);

        if (empty($all)) {
            $this->command->error('No data returned. Check your internet connection.');
            return;
        }

        $data = [];

        foreach ($all as $town) {
            // This API uses 'code' and 'name'
            // It does NOT provide Zip/District, so we leave them NULL for now
            $data[] = [
                'name' => $town['name'],
                'municity_code' => $town['code'],
                'district' => 1,
                'zip_code' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 4. INSERT
        DB::table('municipalities')->upsert($data, ['municity_code'], ['name']);

        $this->command->info('Success! Seeded ' . count($data) . ' towns (Municipalities & Cities).');
    }
}

<?php

namespace Database\Seeders;

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
        $this->command->info('Fetching Bataan Cities and Municipalities from psgc.cloud API v2...');

        // FETCH CITIES & MUNICIPALITIES for Bataan
        $response = Http::withoutVerifying()
            ->get('https://psgc.cloud/api/v2/provinces/Bataan/cities-municipalities');

        $towns = $response->json('data') ?? $response->json();

        if (empty($towns)) {
            $this->command->error('No data returned. Check your internet connection or the endpoint.');
            return;
        }

        $data = [];

        foreach ($towns as $town) {
            $data[] = [
                'name' => trim($town['name']),
                'municity_code' => $town['code'],
                'district' => isset($town['district']) ? (int) filter_var($town['district'], FILTER_SANITIZE_NUMBER_INT) : null,
                'zip_code' => isset($town['zip_code']) ? $town['zip_code'] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // INSERT OR UPDATE
        DB::table('municipalities')->upsert($data, ['municity_code'], ['name', 'district', 'zip_code']);

        $this->command->info('Success! Seeded ' . count($data) . ' towns (Municipalities & Cities).');
    }
}

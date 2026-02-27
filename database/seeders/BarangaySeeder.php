<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class BarangaySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Loading local municipalities map...');

        // Build a name-based mapping to resolve city_municipality strings to our local IDs.
        $normalizeName = function (?string $name): string {
            $name = $name ?? '';
            $name = preg_replace('/^City of\s+/i', '', $name);
            return trim(strtoupper($name));
        };

        $localNameMap = DB::table('municipalities')->get(['id', 'name'])
            ->mapWithKeys(function ($row) use ($normalizeName) {
                return [$normalizeName($row->name) => $row->id];
            });

        $this->command->info('Fetching Bataan Barangays from psgc.cloud API v2...');

        // Fetch ALL barangays for Bataan in one shot
        $response = Http::withoutVerifying()
            ->get('https://psgc.cloud/api/v2/provinces/Bataan/barangays');

        if ($response->failed()) {
            $this->command->error('API Request Failed. Check connection or endpoint.');
            return;
        }

        $barangays = $response->json('data') ?? $response->json() ?? [];
        $data = [];

        foreach ($barangays as $item) {
            $cityName = $item['city_municipality'] ?? null;
            $localId = $localNameMap[$normalizeName($cityName)] ?? null;

            if ($localId) {
                $data[] = [
                    'name' => trim($item['name']),
                    'barangay_code' => $item['code'],
                    'municity_code' => $localId, // Linked Foreign Key
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (empty($data)) {
            $this->command->error('No barangays found to seed. Make sure municipalities are seeded first.');
            return;
        }

        // Bulk Insert (Upsert prevents duplicates based on unique 'code')
        DB::table('barangays')->upsert($data, ['barangay_code'], ['name', 'municity_code']);

        $this->command->info('Success! Seeded ' . count($data) . ' Barangays for Bataan.');
    }
}
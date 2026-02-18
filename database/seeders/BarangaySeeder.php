<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class BarangaySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Load Local Municipalities map for Foreign Key linking
        // We need to link API codes (e.g., "030801000") to your local DB IDs (e.g., 1, 2, 3)
        $this->command->info('Loading local municipalities map...');

        // Key = PSGC Code, Value = Local DB ID
        $municipalityMap = DB::table('municipalities')->pluck('id', 'municity_code');

        // Some barangay parent codes come from the cities-municipalities list (e.g., Balanga).
        // Build a name-based mapping so we can resolve those codes to local IDs.
        $normalizeName = function (?string $name): string {
            $name = $name ?? '';
            $name = preg_replace('/^City of\s+/i', '', $name);
            return trim(strtoupper($name));
        };

        $localNameMap = DB::table('municipalities')->get(['id', 'name'])
            ->mapWithKeys(function ($row) use ($normalizeName) {
                return [$normalizeName($row->name) => $row->id];
            });

        $codeToLocalId = [];
        $towns = Http::withoutVerifying()
            ->get('https://psgc.gitlab.io/api/provinces/030800000/cities-municipalities/')
            ->json();

        foreach ($towns ?? [] as $town) {
            $code = $town['municity_code'] ?? null;
            $name = $town['name'] ?? null;
            if (!$code || !$name) {
                continue;
            }
            $localId = $localNameMap[$normalizeName($name)] ?? null;
            if ($localId) {
                $codeToLocalId[$code] = $localId;
            }
        }

        $this->command->info('Fetching Bataan Barangays from psgc.gitlab.io...');

        // 2. Fetch province barangays (municipalities)
        $response = Http::withoutVerifying()
            ->get('https://psgc.gitlab.io/api/provinces/030800000/barangays/');

        if ($response->failed()) {
            $this->command->error('API Request Failed. Check connection.');
            return;
        }

        $barangays = $response->json() ?? [];

        // 3. Fetch city barangays (Balanga is a city in Bataan)
        $cities = Http::withoutVerifying()
            ->get('https://psgc.gitlab.io/api/provinces/030800000/cities/')
            ->json();

        foreach ($cities ?? [] as $city) {
            $cityCode = $city['municity_code'] ?? null;
            if (!$cityCode) {
                continue;
            }

            $cityResponse = Http::withoutVerifying()
                ->get("https://psgc.gitlab.io/api/cities/{$cityCode}/barangays/");

            if ($cityResponse->failed()) {
                continue;
            }

            $cityBarangays = $cityResponse->json() ?? [];
            foreach ($cityBarangays as $item) {
                if (!isset($item['cityCode'])) {
                    $item['cityCode'] = $cityCode;
                }
                $barangays[] = $item;
            }
        }
        $data = [];

        foreach ($barangays as $item) {
            // The API provides the parent code as either 'municipalityCode' or 'cityCode'
            $parentCode = $item['municipalityCode'] ?? null;
            if (!$parentCode) {
                $parentCode = $item['cityCode'] ?? null;
            }

            // Find the matching Local ID from our map
            $localId = $municipalityMap[$parentCode] ?? $codeToLocalId[$parentCode] ?? null;

            if ($localId) {
                $data[] = [
                    'name' => $item['name'],
                    'barangay_code' => $item['code'],
                    'municity_code' => $localId,     // Linked Foreign Key
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (empty($data)) {
            $this->command->error('No barangays found to seed.');
            return;
        }

        // 3. Bulk Insert
        // Upsert prevents duplicates based on the unique 'code'
        DB::table('barangays')->upsert($data, ['code'], ['name', 'municity_code']);

        $this->command->info('Success! Seeded ' . count($data) . ' Barangays for Bataan.');
    }
}
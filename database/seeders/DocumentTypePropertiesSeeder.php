<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypePropertiesSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'code' => 'BRGY_CLR',
                'name' => 'Barangay Clearance',
                'doc_type_model' => 'Clearance',
                'description' => 'General barangay clearance for employment and other purposes.',
                'default_fee' => 0.00,
                'validity_days' => 180,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'INDIGENCY',
                'name' => 'Certificate of Indigency',
                'doc_type_model' => 'IndigencyCertificate',
                'description' => 'Certificate stating indigent status for social services.',
                'default_fee' => 0.00,
                'validity_days' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'RESIDENCY',
                'name' => 'Certificate of Residency',
                'doc_type_model' => 'ResidencyCertificate',
                'description' => 'Proof of residency within the barangay.',
                'default_fee' => 0.00,
                'validity_days' => 365,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'BUSINESS_CLR',
                'name' => 'Business Clearance',
                'doc_type_model' => 'BusinessClearance',
                'description' => 'Clearance required for business permits and transactions.',
                'default_fee' => 0.00,
                'validity_days' => 365,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'CONSTR_CLR',
                'name' => 'Construction Clearance',
                'doc_type_model' => 'ConstructionClearance',
                'description' => 'Clearance for construction-related permits.',
                'default_fee' => 0.00,
                'validity_days' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($items as $item) {
            DB::table('document_type_properties')->updateOrInsert(
                ['code' => $item['code']],
                $item
            );
        }
    }
}

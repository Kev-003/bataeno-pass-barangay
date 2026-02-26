<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DocumentTypePropertiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $properties = [
            [
                'code' => 'BRGY_BUS',
                'name' => 'Business Clearance',
                'doc_type_model' => 'App\\Models\\BusinessClearance',
                'description' => 'Required for securing or renewing a Mayor\'s Permit for local businesses.',
                'default_fee' => 500.00,
                'validity_days' => 365,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BRGY_CLR',
                'name' => 'Barangay Clearance',
                'doc_type_model' => 'App\\Models\\Clearance',
                'description' => 'General-purpose clearance for employment or local ID requirements.',
                'default_fee' => 100.00,
                'validity_days' => 180,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BRGY_GRD',
                'name' => 'Guardianship Certificate',
                'doc_type_model' => 'App\\Models\\GuardianshipCertificate',
                'description' => 'Attests to the legal or de facto guardianship of a minor/dependent.',
                'default_fee' => 150.00,
                'validity_days' => 180,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BRGY_CON',
                'name' => 'Construction Clearance',
                'doc_type_model' => 'App\\Models\\ConstructionClearance',
                'description' => 'Needed for building renovations or new structures within the barangay.',
                'default_fee' => 300.00,
                'validity_days' => 90,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BRGY_IND',
                'name' => 'Certificate of Indigency',
                'doc_type_model' => 'App\\Models\\IndigencyCertificate',
                'description' => 'For individuals seeking financial, medical, or burial assistance.',
                'default_fee' => 0.00,
                'validity_days' => 60,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BRGY_SPS',
                'name' => 'Indigency SPS Certificate',
                'doc_type_model' => 'App\\Models\\IndigencySPSCertificate',
                'description' => 'Specialized indigency for Social Pension System (SPS) for seniors.',
                'default_fee' => 0.00,
                'validity_days' => 60,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BRGY_FTJ',
                'name' => 'First-Time Jobseeker Certificate',
                'doc_type_model' => 'App\\Models\\JobseekerCertificate',
                'description' => 'Issued per RA 11261 (Free for first-time applicants).',
                'default_fee' => 0.00,
                'validity_days' => 365,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BRGY_RES',
                'name' => 'Residency Certificate',
                'doc_type_model' => 'App\\Models\\ResidencyCertificate',
                'description' => 'Official proof that the person has been a resident for a specific period.',
                'default_fee' => 75.00,
                'validity_days' => 180,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BRGY_SOL',
                'name' => 'Solo Parent Certificate',
                'doc_type_model' => 'App\\Models\\SoloParentCertificate',
                'description' => 'Verification of status for availing Solo Parent Act benefits.',
                'default_fee' => 100.00,
                'validity_days' => 180,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BRGY_TRI',
                'name' => 'Tricycle Clearance',
                'doc_type_model' => 'App\\Models\\TricycleClearance',
                'description' => 'Required for MTOP (Motorized Tricycle Operator\'s Permit) application.',
                'default_fee' => 150.00,
                'validity_days' => 365,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        foreach ($properties as $property) {
            DB::table('document_type_properties')->updateOrInsert(
                ['code' => $property['code']],
                $property
            );
        }
    }
}

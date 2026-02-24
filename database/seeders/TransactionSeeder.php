<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DocumentTransaction;
use App\Models\DocumentTypeProperty;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure at least one Document Type exists
        $docType = DocumentTypeProperty::firstOrCreate(
            ['code' => 'BRGY_CLR'],
            [
                'name' => 'Barangay Clearance',
                'description' => 'Clearance for general purposes',
                'default_fee' => 50,
                'validity_days' => 180
            ]
        );

        $residents = User::whereNotNull('barangay_code')->get();

        if ($residents->isEmpty()) {
            $this->command->error('No residents with barangay_code found.');
            return;
        }

        $this->command->info('Seeding transactions for ' . $residents->count() . ' residents...');

        foreach ($residents as $resident) {
            // Create 5 transactions per resident
            for ($i = 0; $i < 5; $i++) {
                DocumentTransaction::create([
                    'document_type_id' => $docType->id,
                    'requester_id' => $resident->id,
                    // Use barangay_code as IDs if that's the system design, or try to map relevantly. 
                    // Based on schema comments, this points to Jurisdiction Code.
                    'barangay_id' => $resident->barangay_code,
                    'request_origin' => 'web',
                    'status' => 'pending',
                    'checksum' => Str::random(64),
                    // 'purpose' => 'Test Request ' . ($i + 1),
                ]);
            }
        }

        $this->command->info('Transactions seeded successfully.');
    }
}

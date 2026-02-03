<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Barangay;
use App\Models\BarangayTerm;
use App\Models\DocumentTypeProperty;
use App\Models\DocumentRequirementsDefinition;
use App\Models\Delegation;
use App\Models\DocumentTransaction;
use App\Models\Clearance;
use App\Models\TransactionRequirement;

class DocumentTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Setup Context
        $barangay = Barangay::where('name', 'San Jose')->first();
        if (!$barangay) {
            $this->command->warn("Barangay San Jose not found. Skipping DocumentTransactionSeeder.");
            return;
        }

        // 2. Create Actors
        // A. The Captain
        $kapitan = User::factory()->create([
            'first_name' => 'Tiago',
            'last_name' => 'Kapitan',
            'email' => 'kapitan@bataeno.ph'
        ]);
        $kapTerm = BarangayTerm::create([
            'barangay_id' => $barangay->id,
            'user_id' => $kapitan->id,
            'position_type' => 'Captain',
            'started_at' => now()->subMonth(),
        ]);

        // B. The Secretary (Delegate)
        $secretary = User::factory()->create([
            'first_name' => 'Maria',
            'last_name' => 'Secretary',
            'email' => 'secretary@bataeno.ph'
        ]);
        $secTerm = BarangayTerm::create([
            'barangay_id' => $barangay->id,
            'user_id' => $secretary->id,
            'position_type' => 'Secretary',
            'started_at' => now()->subMonth(),
        ]);

        // C. The Citizen (Requester)
        $citizen = User::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Citizen',
            'email' => 'juan@bataeno.ph'
        ]);
        // Also give him a resident term
        BarangayTerm::create([
            'barangay_id' => $barangay->id,
            'user_id' => $citizen->id,
            'position_type' => 'Resident',
            'started_at' => now()->subYears(2),
        ]);


        // 3. Setup Document Types & Rules
        $docType = DocumentTypeProperty::firstOrCreate(
            ['code' => 'BRGY_CLR'],
            [
                'name' => 'Barangay Clearance',
                'description' => 'General purpose clearance',
                'default_fee' => 50.00,
                'validity_days' => 90
            ]
        );

        $reqCedula = DocumentRequirementsDefinition::firstOrCreate(
            ['requirement_name' => 'Cedula / CTC'],
            ['data_type' => 'image', 'description' => 'Photo of current Community Tax Certificate']
        );

        // Link Requirement to Document Type (Pivot)
        if (!$docType->requirements()->where('requirement_id', $reqCedula->id)->exists()) {
            $docType->requirements()->attach($reqCedula->id);
        }

        // 4. Create Delegation (Captain -> Secretary for Clearance)
        Delegation::create([
            'granter_term_id' => $kapTerm->id,
            'delegate_term_id' => $secTerm->id,
            'document_type_id' => $docType->id,
            'expires_at' => now()->addYear()
        ]);
        $this->command->info("Delegation created: Captain Tiago -> Secretary Maria for Barangay Clearance");


        // 5. Simulate Transaction: STEP 1 - Application/Request
        $transaction = DocumentTransaction::create([
            'barangay_id' => $barangay->id,
            'document_type_id' => $docType->id,
            'requester_id' => $citizen->id,
            'request_origin' => 'Walk-in',
            'status' => 'PENDING',
            'checksum' => hash('sha256', microtime()) // Placeholder checksum
        ]);

        // Create the specific Clearance details
        Clearance::create([
            'transaction_id' => $transaction->id,
            'gender' => $citizen->gender,
            'civil_status' => $citizen->civil_status,
            'housing_unit' => 'Unit 101',
            'street' => 'Rizal St.',
            'subdivision' => 'Bataan Heights',
            'community_tax_id' => 'CTC-12345678',
            'purpose' => 'Employment'
        ]);

        // Submit Requirement
        TransactionRequirement::create([
            'transaction_id' => $transaction->id,
            'requirement_id' => $reqCedula->id,
            'file_path' => '/uploads/documents/ctc_juan.jpg',
            'is_verified' => false
        ]);

        $this->command->info("Transaction #{$transaction->id} created (PENDING) for Juan Citizen.");


        // 6. Simulate Transaction: STEP 2 - Approval by Secretary (Delegated)

        // Logic: Secretary reviews, verifies requirements, then approves
        $transReq = TransactionRequirement::where('transaction_id', $transaction->id)->first();
        $transReq->update(['is_verified' => true]);

        $transaction->update([
            'status' => 'APPROVED',
            'approver_id' => $secTerm->id,      // Signed by Secretary
            'on_behalf_of' => $kapTerm->id,     // On behalf of Captain
            'signing_capacity' => 'By Authority of the Punong Barangay',
            'issued_at' => now(),
            'expiry_date' => now()->addDays($docType->validity_days)
        ]);

        $this->command->info("Transaction #{$transaction->id} APPROVED by Secretary Maria (Delegated).");
    }
}

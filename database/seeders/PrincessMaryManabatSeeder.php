<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\User;
use App\Models\BarangayTerm;

class PrincessMaryManabatSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $email = 'princessmarybituaamianit_20101202@1bataan.gov.ph';

        // Update or create the user as a resident only (no official roles/terms)
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'uuid' => '8421ece2-a06b-45da-9f74-cbf9affa3f90',
                'first_name' => 'PRINCESS MARY',
                'middle_name' => 'BITUA',
                'last_name' => 'AMIANIT',
                'date_of_birth' => '2010-12-02',
                'place_of_birth' => '-',
                'gender' => 'FEMALE',
                'civil_status' => 'SINGLE',
                'email_verified_at' => $now,
                // do not modify password here
                'municity_name' => 'Pilar',
                'barangay_name' => 'Pilar',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Remove any official roles and barangay terms so the user is resident-only
        try {
            $user->syncRoles([]);
        } catch (\Throwable $e) {
            // ignore if roles package not available
        }

        // Delete all BarangayTerm records for this user
        BarangayTerm::where('user_id', $user->id)->delete();

        $this->command->info("Ensured {$email} is resident-only (roles cleared, barangay terms removed)");
    }
}

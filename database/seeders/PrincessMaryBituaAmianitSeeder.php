<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PrincessMaryBituaAmianitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Get Pilar municipality
        $pilar = DB::table('municipalities')->where('name', 'Pilar')->first();
        if (!$pilar) {
            $this->command->error('Pilar municipality not found. Please run BarangaySeeder first.');
            return;
        }

        // Get Rizal barangay in Pilar (or Kaparangan if Rizal doesn't exist)
        $barangay = DB::table('barangays')
            ->where('municity_code', $pilar->id)
            ->where('name', 'Rizal')
            ->first();

        if (!$barangay) {
            $barangay = DB::table('barangays')
                ->where('municity_code', $pilar->id)
                ->where('name', 'Kaparangan')
                ->first();
        }

        if (!$barangay) {
            $this->command->error('Rizal or Kaparangan barangay in Pilar not found.');
            return;
        }

        // Create or update the user
        $user = User::updateOrCreate(
            ['uuid' => '8421ece2-a06b-45da-9f74-cbf9affa3f90'],
            [
                'first_name' => 'PRINCESS MARY',
                'middle_name' => 'BITUA',
                'last_name' => 'AMIANIT',
                'date_of_birth' => '2010-12-02',
                'place_of_birth' => 'Bataan, Pilar, Rizal',
                'gender' => 'FEMALE',
                'civil_status' => 'SINGLE',
                'email' => 'princessmarybituaamianit_20101202@1bataan.gov.ph',
                'password' => Hash::make('password'),
                'email_verified_at' => $now,
                'barangay_id' => $barangay->id,
                'municity_id' => $pilar->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->command->info('✓ Seeded PRINCESS MARY BITUA AMIANIT');
        $this->command->info("  UUID: 8421ece2-a06b-45da-9f74-cbf9affa3f90");
        $this->command->info("  Email: princessmarybituaamianit_20101202@1bataan.gov.ph");
        $this->command->info("  Birthday: December 2, 2010");
        $this->command->info("  Gender: FEMALE");
        $this->command->info("  Civil Status: SINGLE");
        $this->command->info("  Location: {$barangay->name}, Pilar, Bataan");
        $this->command->info("  Default Password: 'password'");
    }
}

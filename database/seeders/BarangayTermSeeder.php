<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BarangayTerm;
use App\Models\Barangay;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class BarangayTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get Roles
        $captainRole = Role::where('name', 'Captain')->first();
        $kagawadRole = Role::where('name', 'Kagawad')->first();
        $secretaryRole = Role::where('name', 'Secretary')->first();

        if (!$captainRole || !$kagawadRole || !$secretaryRole) {
            $this->command->error('Required roles (Captain, Kagawad, Secretary) not found. Run RolesAndPermissionsSeeder first.');
            return;
        }

        // 2. Get User ID 1 (The user is the Captain)
        $captainUser = User::find(1);
        if (!$captainUser) {
            $this->command->error('User with ID 1 not found.');
            return;
        }

        // 3. Get Santo Domingo Barangay
        $barangay = Barangay::where('name', 'Santo Domingo')->first();
        if (!$barangay) {
            $this->command->error('Santo Domingo barangay not found.');
            return;
        }

        // 4. Assign Captain and Sync User Location
        // We ensure the user's resident barangay_code matches the 9-digit PSGC in our table
        $captainUser->update([
            'barangay_id' => $barangay->id,
            'municity_id' => $barangay->municity_id,
        ]);

        BarangayTerm::updateOrCreate(
            ['user_id' => $captainUser->id, 'position_id' => $captainRole->id],
            ['barangay_id' => $barangay->id, 'started_at' => now()]
        );
        $captainUser->assignRole($captainRole);
        $this->command->info("Assigned Captain: {$captainUser->first_name} {$captainUser->last_name}");

        // 5. Get residents of Santo Domingo (excluding the captain) to be officials
        $santoDomingoResidents = User::where('barangay_id', $barangay->id)
            ->where('id', '!=', $captainUser->id)
            ->limit(8) // 7 Kagawads + 1 Secretary
            ->get();

        if ($santoDomingoResidents->count() < 8) {
            $this->command->warn('Not enough residents in Santo Domingo to fill all official positions (need at least 8).');
        }

        // 6. Assign 7 Kagawads
        $kagawads = $santoDomingoResidents->take(7);
        foreach ($kagawads as $kagawad) {
            BarangayTerm::updateOrCreate(
                ['user_id' => $kagawad->id, 'position_id' => $kagawadRole->id],
                ['barangay_id' => $barangay->id, 'started_at' => now()]
            );
            $kagawad->assignRole($kagawadRole);
            $this->command->info("Assigned Kagawad: {$kagawad->first_name} {$kagawad->last_name}");
        }

        // 7. Assign 1 Secretary
        $secretary = $santoDomingoResidents->skip(7)->first();
        if ($secretary) {
            BarangayTerm::updateOrCreate(
                ['user_id' => $secretary->id, 'position_id' => $secretaryRole->id],
                ['barangay_id' => $barangay->id, 'started_at' => now()]
            );
            $secretary->assignRole($secretaryRole);
            $this->command->info("Assigned Secretary: {$secretary->first_name} {$secretary->last_name}");
        }

        $this->command->info("Barangay officials for {$barangay->name} seeded successfully.");
    }
}

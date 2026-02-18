<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BarangayTerm;
use App\Models\Barangay;
use App\Models\User;
use Spatie\Permission\Models\Role;

class BarangayTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get the Captain Role
        // We use firstOrCreate to ensure it exists if the roles haven't been seeded yet
        $role = Role::where(['name' => 'Captain', 'guard_name' => 'web'])->first();

        // 2. Get User ID 1
        $user = User::find(1);

        if (!$user) {
            $this->command->error('User with ID 1 not found. Please register or seed a user first.');
            return;
        }

        // 3. Get the Barangay to assign the user to
        // We'll prioritize the barangay the user is already registered in
        $barangay = Barangay::where('barangay_code', $user->barangay_code)->first() ?? Barangay::first();

        if (!$barangay) {
            $this->command->error('No barangays found. Please run BarangaySeeder first.');
            return;
        }

        // 4. Create the Barangay Term
        // Note: The 'barangay_code' column in barangay_terms is currently a foreignId
        // which points to the 'id' of the barangays table in the current migration.
        BarangayTerm::updateOrCreate(
            [
                'user_id' => $user->id,
                'position_id' => $role->id,
            ],
            [
                'barangay_code' => 200,
                'started_at' => now(),
            ]
        );

        $this->command->info("Success! User 1 is now the Captain of {$barangay->name}.");
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Municipality;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class CityAdminAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        $cityAdminRole = Role::where('name', 'City Admin')->first();
        $adminRole     = Role::where('name', 'Admin')->first();
        $superAdminRole = Role::where('name', 'Super Admin')->first();

        if (!$cityAdminRole || !$adminRole || !$superAdminRole) {
            $this->command->error('Required roles not found. Run RolesAndPermissionsSeeder first.');
            return;
        }

        // --- City Admin ---
        // Pick a user that has no roles yet and assign City Admin
        $cityAdminUser = User::doesntHave('roles')->first();

        if (!$cityAdminUser) {
            $this->command->error('No unassigned user found for City Admin.');
            return;
        }

        // Optionally tie them to a municipality
        $municipality = Municipality::first();
        if ($municipality) {
            $cityAdminUser->update(['municity_id' => $municipality->id]);
        }

        $cityAdminUser->assignRole($cityAdminRole);
        $this->command->info("Assigned City Admin: {$cityAdminUser->first_name} {$cityAdminUser->last_name}");

        // --- Admin ---
        $adminUser = User::doesntHave('roles')->first();

        if (!$adminUser) {
            $this->command->error('No unassigned user found for Admin.');
            return;
        }

        $adminUser->assignRole($adminRole);
        $this->command->info("Assigned Admin: {$adminUser->first_name} {$adminUser->last_name}");

        // --- Super Admin ---
        $superAdminUser = User::doesntHave('roles')->first();

        if (!$superAdminUser) {
            $this->command->error('No unassigned user found for Super Admin.');
            return;
        }

        $superAdminUser->assignRole($superAdminRole);
        $this->command->info("Assigned Super Admin: {$superAdminUser->first_name} {$superAdminUser->last_name}");
    }
}
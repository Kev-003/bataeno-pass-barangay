<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // 1. Define the Permission "Blocks" (The Bases)
        $residentBase = [
            'request documents',
            'view my documents',
            'view my profile',
        ];

        $householdHead = [      //FUTURE IMPLEMENTATION: prioritize documents
            'edit my household',
            'edit my family',
        ];

        $officialBase = [
            'view resident info',
            'view household info',
            'view family info',
        ];

        $administrationBase = [
            'manage users',
        ];

        // 2. Define the Role-to-Permission Mapping
        $rolePermissions = [
            'Resident' => $residentBase,

            'Household Head' => array_merge($residentBase, $householdHead),

            'Secretary' => array_merge($officialBase, [
                'make requests for residents'
            ]),

            'Kagawad' => array_merge($officialBase, [
                'approve requests',
                'delegate authority'
            ]),

            'Captain' => array_merge($officialBase, [
                'approve requests',
                'delegate authority',
                'manage my officials',
                'manage my barangay info'
            ]),

            'City Admin' => [
                'manage officials',
                'manage barangay info'
            ],

            'Admin' => $administrationBase, // Correctly excluded from Barangay tasks

            'Super Admin' => ['manage admins'], // Minimalist; usually handled via Gate::before
        ];

        // 3. Register everything in the Database
// First, create a flat list of ALL unique permissions to seed them
        $allUniquePermissions = collect($rolePermissions)->flatten()->unique();

        foreach ($allUniquePermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Second, create the roles and sync the permissions
        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::create(['name' => $roleName]);
            $role->givePermissionTo($permissions);
        }
    }
}

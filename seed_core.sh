#!/bin/bash

echo "🚀 Running fresh migration..."
php artisan migrate:fresh

echo "==============================="
echo "🌱 Seeding Data in Order"
echo "==============================="


echo "==============================="
echo "Core Data"
echo "==============================="

echo "1: Seeding Municipalities..."
php artisan db:seed --class=MunicipalitySeeder

echo "2: Seeding Barangays..."
php artisan db:seed --class=BarangaySeeder

echo "3: Seeding Roles and Permissions..."
php artisan db:seed --class=RolesAndPermissionsSeeder

echo "4: Seeding Document Type Properties..."
php artisan db:seed --class=DocumentTypePropertiesSeeder

echo "==============================="
echo "Testing Data [REMOVE ON PRODUCTION]"
echo "==============================="

echo "5: Seeding Houses..."
php artisan db:seed --class=HouseSeeder

echo "6: Seeding Households..."
php artisan db:seed --class=HouseholdSeeder

echo "7: Seeding Families..."
php artisan db:seed --class=FamilySeeder

echo "8: Seeding Residents..."
php artisan db:seed --class=ResidentSeeder

echo "9. Seeding Barangay Terms..."
php artisan db:seed --class=BarangayTermSeeder

echo "10: Building Lineage (family_id, parents, deceased)..."
php artisan db:seed --class=LineageSeeder

echo "==============================="
echo "✅ Fresh Migration and Seeding Complete!"
echo "==============================="

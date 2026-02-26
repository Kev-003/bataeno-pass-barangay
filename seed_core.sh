#!/bin/bash

echo "🚀 Running fresh migration..."
php artisan migrate:fresh

echo "==============================="
echo "🌱 Seeding Core Data in Order"
echo "==============================="

echo "1/4: Seeding Municipalities..."
php artisan db:seed --class=MunicipalitySeeder

echo "2/4: Seeding Barangays..."
php artisan db:seed --class=BarangaySeeder

echo "3/4: Seeding Roles and Permissions..."
php artisan db:seed --class=RolesAndPermissionsSeeder

echo "4/4: Seeding Document Type Properties..."
php artisan db:seed --class=DocumentTypePropertiesSeeder

echo "==============================="
echo "✅ Fresh Migration and Seeding Complete!"
echo "==============================="

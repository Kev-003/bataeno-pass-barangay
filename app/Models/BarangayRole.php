<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class BarangayRole extends Role
{
    // Barangay official positions only
    public static function officialPositions(): array
    {
        return ['Captain', 'Secretary', 'Kagawad'];
    }

    public static function officialPositionOptions(): array
    {
        return array_combine(
            static::officialPositions(),
            static::officialPositions()
        );
    }

    public static function isOfficialPosition(string $name): bool
    {
        return in_array($name, static::officialPositions());
    }

    public static function officialRoles()
    {
        return static::whereIn('name', static::officialPositions())->get();
    }
}

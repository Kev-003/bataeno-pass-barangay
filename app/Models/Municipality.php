<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'district',
        'zip_code',
        'municity_code',
        'province_code',
        'province_name',
        'region_code',
        'region_name',
    ];

    public function barangays()
    {
        return $this->hasMany(Barangay::class);
    }
}

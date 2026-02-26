<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Barangay extends Model
{
    use HasFactory;



    protected $fillable = [
        'name',
        'barangay_code',
        'municity_code',
        'province_code',
        'region_code',
    ];

    /**
     * The barangays.municity_code column stores the local municipalities.id (integer FK).
     * Path: barangays.municity_code -> municipalities.id -> municipalities.municity_code
     */
    public function municipality()
    {
        return $this->belongsTo(Municipality::class, 'municity_code', 'id');
    }

    /**
     * Alternative lookup via matching string PSGC codes (if both tables have matching code strings).
     */
    public function municipalityByCode()
    {
        return $this->belongsTo(Municipality::class, 'municity_code', 'municity_code');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'barangay_id');
    }

    public function getProvinceAttribute()
    {
        return $this->municipalityByCode->province_name ?? 'Bataan';
    }

    public function getCityAttribute()
    {
        return $this->municipalityByCode->name ?? '';
    }

    public function getAddressAttribute()
    {
        return "Barangay Hall, " . $this->name;
    }

    public function getContactNumberAttribute()
    {
        return "N/A";
    }
}

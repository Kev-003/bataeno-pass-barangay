<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Barangay extends Model
{
    use HasFactory;

    /**
     * Normalize PSGC codes from eGovPH (10 digits) to standard PSGC (9 digits).
     * eGovPH: RR-PPP-MM-BBB (e.g. 03-008-10-024)
     * PSGC:   RR-PP-MM-BBB  (e.g. 03-08-10-024)
     */
    public static function normalizeCode(?string $code): ?string
    {
        if (!$code)
            return null;

        if (strlen($code) === 10 && substr($code, 2, 1) === '0') {
            return substr($code, 0, 2) . substr($code, 3);
        }

        return $code;
    }

    protected $fillable = [
        'municipality_id',
        'name',
        'barangay_code',
        'municity_code',
        'province_code',
        'region_code',
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class, 'municipality_id');
    }

    public function municipalityByCode()
    {
        return $this->belongsTo(Municipality::class, 'municity_code', 'municity_code');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'barangay_code');
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

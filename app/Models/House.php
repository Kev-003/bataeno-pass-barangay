<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    use HasFactory;

    protected $fillable = [
        'barangay_id',
        'barangay_code',
        'municity_code',
        'housing_unit',
        'street',
        'subdivision',
        'barangay',
    ];

    public function households()
    {
        return $this->hasMany(Household::class, 'house_id');
    }

    public function linkedBarangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_code', 'barangay_code');
    }
}

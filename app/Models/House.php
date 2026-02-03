<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    use HasFactory;

    protected $fillable = [
        'barangay_id',
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
}

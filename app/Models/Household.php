<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Household extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'household_head_id',
        'ownership',
        'monthly_utility_expense',
        'total_income',
    ];

    public function house()
    {
        return $this->belongsTo(House::class, 'house_id');
    }

    public function headOfHousehold()
    {
        return $this->belongsTo(HouseholdMemberProfile::class, 'household_head_id');
    }

    public function members()
    {
        return $this->hasMany(HouseholdMemberProfile::class, 'household_id');
    }

    public function barangay()
    {
        return $this->hasOneThrough(
            Barangay::class,
            House::class,
            'id', // Foreign key on houses table (PK of house)
            'id', // Foreign key on barangays table (PK of barangay)
            'house_id', // Local key on households table
            'barangay_id' // Local key on houses table
        );
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_name',
        'household_id',
        'barangay_id',
        'father_id',
        'mother_id',
    ];

    public function father()
    {
        return $this->belongsTo(User::class, 'father_id')->withTrashed();
    }

    public function mother()
    {
        return $this->belongsTo(User::class, 'mother_id')->withTrashed();
    }

    public function members()
    {
        return $this->hasMany(User::class, 'family_id');
    }

    public function household()
    {
        return $this->belongsTo(Household::class, 'household_id');
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    /**
     * Accessor for 'name' to satisfy views expecting 'name' instead of 'family_name'.
     */
    public function getNameAttribute(): string
    {
        return $this->family_name;
    }
}

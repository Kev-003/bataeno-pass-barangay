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

    public function users()
    {
        return $this->hasMany(User::class, 'barangay_id');
    }

    public function getProvinceAttribute(): string
    {
        return $this->municipality->province_name ?? 'Bataan';
    }

    public function getCityAttribute(): string
    {
        return $this->municipality->name ?? '';
    }

    public function getAddressAttribute()
    {
        return "Barangay Hall, " . $this->name;
    }

    public function getContactNumberAttribute()
    {
        return "N/A";
    }

    public function families()
    {
        return $this->hasMany(Family::class, 'barangay_id');
    }

    public function houses()
    {
        return $this->hasMany(House::class, 'barangay_id');
    }

    public function getAllHouseholdsCountAttribute(): int
    {
        return $this->houses()->withCount('households')->get()->sum('households_count');
    }

    public function delegations()
    {
        return $this->hasManyThrough(
            \App\Models\Delegation::class,
            \App\Models\BarangayTerm::class,
            'barangay_id',      // Foreign key on BarangayTerm table
            'granter_term_id',   // Foreign key on Delegation table
            'id',               // Local key on Barangay table
            'id'                // Local key on BarangayTerm table
        );
    }

    public function activeCaptain()
    {
        return $this->hasOne(\App\Models\BarangayTerm::class, 'barangay_id')
            ->whereHas('position', fn($q) => $q->where('name', 'Captain'))
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>=', now());
            });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delegation extends Model
{
    use HasFactory;

    protected $fillable = [
        'granter_term_id',
        'delegate_term_id',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function granterTerm()
    {
        return $this->belongsTo(BarangayTerm::class, 'granter_term_id');
    }

    public function delegateTerm()
    {
        return $this->belongsTo(BarangayTerm::class, 'delegate_term_id');
    }

    public function barangay()
    {
        return $this->hasOneThrough(
            Barangay::class,
            BarangayTerm::class,
            'id', // Foreign key on barangay_terms table...
            'id', // Foreign key on barangays table...
            'granter_term_id', // Local key on delegations table...
            'barangay_id' // Local key on barangay_terms table...
        );
    }
}

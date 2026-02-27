<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResidencyRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'barangay_id',
        'household_id', // Added for joining existing households
        'housing_unit',
        'street',
        'subdivision',
        'role',
        'membership_type',
        'ownership',
        'status',
        'rejection_reason',
        'approver_id',
        'actioned_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}

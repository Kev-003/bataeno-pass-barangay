<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HouseholdMemberProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'household_id',
        'role',
        'membership_type',
        'presence_status',
        'economic_contribution',
        'monthly_income',
        'started_at',
        'ended_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function household()
    {
        return $this->belongsTo(Household::class, 'household_id');
    }
}

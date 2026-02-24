<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Barangay;
use Spatie\Permission\Contracts\Role;

class BarangayTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'barangay_code',
        'position_id',
        'started_at',
        'ended_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_code');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function position()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'position_id');
    }

    /**
     * Accessor for name to delegate to the linked User.
     */
    public function getNameAttribute()
    {
        return $this->user->name;
    }

}

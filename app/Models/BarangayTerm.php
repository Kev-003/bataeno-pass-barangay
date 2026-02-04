<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Barangay;

class BarangayTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'barangay_id',
        'user_id',
        'position_type',
        'started_at',
        'ended_at'
    ];

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}

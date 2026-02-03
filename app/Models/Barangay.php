<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Barangay extends Model
{
    use HasFactory;

    protected $fillable = [
        'municipality_id',
        'name',

    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}

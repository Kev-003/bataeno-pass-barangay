<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_name',
        'clan_origin',
    ];

    public function members()
    {
        return $this->hasMany(User::class, 'family_id');
    }
}

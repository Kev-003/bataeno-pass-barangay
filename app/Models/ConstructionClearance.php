<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConstructionClearance extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'location'
    ];

    public function transaction()
    {
        return $this->belongsTo(DocumentTransaction::class, 'transaction_id');
    }
}

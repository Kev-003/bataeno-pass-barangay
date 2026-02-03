<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessClearance extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'business_name',
        'business_type',
        'ownership',
        'services',
        'location'
    ];

    public function transaction()
    {
        return $this->belongsTo(DocumentTransaction::class, 'transaction_id');
    }
}

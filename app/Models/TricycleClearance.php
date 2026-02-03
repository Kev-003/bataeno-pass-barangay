<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TricycleClearance extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'new_owner_id',
        'requested_for_id',
        'purpose',
        'body_number'
    ];

    public function transaction()
    {
        return $this->belongsTo(DocumentTransaction::class, 'transaction_id');
    }
}

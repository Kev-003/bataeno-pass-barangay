<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResidencyCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'requested_for',
        'length_of_residence'
    ];

    public function transaction()
    {
        return $this->belongsTo(DocumentTransaction::class, 'transaction_id');
    }
}

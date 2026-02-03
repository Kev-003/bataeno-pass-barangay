<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuardianshipCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'guardian_id',
        'relationship',
        'address_id'
    ];

    public function transaction()
    {
        return $this->belongsTo(DocumentTransaction::class, 'transaction_id');
    }

    public function address()
    {
        return $this->belongsTo(Barangay::class, 'address_id');
    }
}

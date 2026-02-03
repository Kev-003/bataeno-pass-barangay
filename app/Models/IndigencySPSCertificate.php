<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndigencySPSCertificate extends Model
{
    use HasFactory;

    protected $table = 'indigencysps_certificates';

    protected $fillable = [
        'transaction_id',
        'father',
        'mother',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoloParentCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'solo_parent_name',
        'no_of_child',
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

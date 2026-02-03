<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobseekerCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id'
    ];

    public function transaction()
    {
        return $this->belongsTo(DocumentTransaction::class, 'transaction_id');
    }
}

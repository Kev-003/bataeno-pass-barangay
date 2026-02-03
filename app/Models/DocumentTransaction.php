<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'approver_id',
        'on_behalf_of',
        'document_type_id',
        'signing_capacity',
        'issued_at',
        'expiry_date',
        'status',
        'request_origin',
        'requester_id',
        'barangay_id',
        'checksum'
    ];

    public function approver()
    {
        return $this->belongsTo(BarangayTerm::class, 'approver_id');
    }

    public function signatory()
    {
        return $this->belongsTo(BarangayTerm::class, 'on_behalf_of');
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentTypeProperty::class, 'document_type_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }
}

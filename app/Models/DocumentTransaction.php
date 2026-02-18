<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DocumentTypeProperty;

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
        'barangay_code',
        'purpose',
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

    public function documentTypeProperty()
    {
        return $this->belongsTo(DocumentTypeProperty::class, 'document_type_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_code');
    }

    public function documentType()
    {
        return $this->documentTypeProperty();
    }

    /**
     * Fetch the dynamic model instance associated with this transaction.
     */
    public function getSpecificDetails()
    {
        $modelClass = $this->documentTypeProperty->doc_type_model;

        if (!str_contains($modelClass, '\\')) {
            $modelClass = "App\\Models\\{$modelClass}";
        }

        if (class_exists($modelClass)) {
            return $modelClass::where('transaction_id', $this->id)->first();
        }

        return null;
    }


}

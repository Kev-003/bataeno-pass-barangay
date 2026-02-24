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
        'checksum',
        'file_path',
        'download_token',
        'issued_at'
    ];

    public function getTemporaryDownloadUrl()
    {
        if (!$this->file_path)
            return null;

        // Generate a fresh token for this specific download attempt
        $token = \Illuminate\Support\Str::random(32);
        $this->update(['download_token' => $token]);

        return \Illuminate\Support\Facades\Storage::disk('documents')
            ->temporaryUrl($this->file_path, now()->addMinutes(5), ['token' => $token]);
    }

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
        return $this->belongsTo(Barangay::class, 'barangay_code', 'id');
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

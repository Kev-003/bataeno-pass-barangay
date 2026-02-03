<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delegation extends Model
{
    use HasFactory;

    protected $fillable = [
        'granter_term_id',
        'delegate_term_id',
        'document_type_id',
        'expires_at'
    ];

    public function granterTerm()
    {
        return $this->belongsTo(BarangayTerm::class, 'granter_term_id');
    }

    public function delegateTerm()
    {
        return $this->belongsTo(BarangayTerm::class, 'delegate_term_id');
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentTypeProperty::class, 'document_type_id');
    }
}

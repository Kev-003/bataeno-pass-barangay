<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTypeProperty extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'default_fee',
        'validity_days'
    ];

    public function requirements()
    {
        return $this->belongsToMany(
            DocumentRequirementsDefinition::class,
            'documents_rules',
            'document_type_id',
            'requirement_id'
        );
    }
}

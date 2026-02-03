<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequirementsDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'requirement_name',
        'data_type',
        'description'
    ];

    public function documentTypeProperties()
    {
        return $this->belongsToMany(
            DocumentTypeProperty::class,
            'documents_rules',
            'requirement_id',
            'document_type_id'
        );
    }
}

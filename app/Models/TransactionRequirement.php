<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'requirement_id',
        'value_text',
        'file_path',
        'is_verified'
    ];

    public function transaction()
    {
        return $this->belongsTo(DocumentTransaction::class, 'transaction_id');
    }

    public function requirement()
    {
        return $this->belongsTo(DocumentRequirementsDefinition::class, 'requirement_id');
    }
}

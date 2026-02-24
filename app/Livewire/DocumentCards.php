<?php

namespace App\Livewire;

use Livewire\Component;

class DocumentCards extends Component
{
    public $transaction;

    public function getDocumentStyles()
    {
        return match ($this->transaction->documentTypeProperty->slug) {
            'barangay-clearance' => [
                'icon' => 'heroicon-o-shield-check',
                'color' => 'blue',
            ],
            'business-clearance' => [
                'icon' => 'heroicon-o-building-storefront',
                'color' => 'indigo',
            ],
            'indigency' => [
                'icon' => 'heroicon-o-hand-raised',
                'color' => 'emerald',
            ],
            'residency' => [
                'icon' => 'heroicon-o-home-modern',
                'color' => 'amber',
            ],
            default => [
                'icon' => 'heroicon-o-document-text',
                'color' => 'gray',
            ],
        };
    }

    public function render()
    {
        return view('livewire.document-cards', [
            'styles' => $this->getDocumentStyles(),
        ]);
    }
}

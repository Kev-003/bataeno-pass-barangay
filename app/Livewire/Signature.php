<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class Signature extends Component
{
    public $image;
    public $barangayCode;
    public $userId;
    public $width = '150px';
    public $height = '80px';

    public function mount($image = null, $barangayCode = null, $userId = null, $width = '150px', $height = '80px')
    {
        $this->image = $image;
        $this->barangayCode = $barangayCode;
        $this->userId = $userId;
        $this->width = $width;
        $this->height = $height;

        if (!$this->image && $this->barangayCode && $this->userId) {
            $this->loadFromStorage();
        }
    }

    protected function loadFromStorage()
    {
        $path = "barangay-assets/{$this->barangayCode}/signatures/{$this->userId}.jpg";

        if (Storage::exists($path)) {
            $fileData = Storage::get($path);
            $mimeType = Storage::mimeType($path);
            $this->image = 'data:' . $mimeType . ';base64,' . base64_encode($fileData);
        }
    }

    public function render()
    {
        return view('livewire.signature');
    }
}

<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class Seal extends Component
{
    public $image;
    public $barangayCode;
    public $width = '100px';
    public $height = '100px';

    public function mount($image = null, $barangayCode = null, $width = '100px', $height = '100px')
    {
        $this->image = $image;
        $this->barangayCode = $barangayCode;
        $this->width = $width;
        $this->height = $height;

        if (!$this->image && $this->barangayCode) {
            $this->loadFromStorage();
        }
    }

    protected function loadFromStorage()
    {
        $path = "barangay-assets/{$this->barangayCode}/seal.png";

        if (Storage::exists($path)) {
            $fileData = Storage::get($path);
            $mimeType = Storage::mimeType($path);
            $this->image = 'data:' . $mimeType . ';base64,' . base64_encode($fileData);
        }
    }

    public function render()
    {
        return view('livewire.seal');
    }
}

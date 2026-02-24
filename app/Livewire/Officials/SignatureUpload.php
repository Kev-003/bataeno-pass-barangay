<?php

namespace App\Livewire\Officials;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Models\Barangay;

class SignatureUpload extends Component
{
    use WithFileUploads;

    public $signature;
    public $currentSignature;

    public function mount()
    {
        $this->loadCurrentSignature();
    }

    protected function loadCurrentSignature()
    {
        $path = auth()->user()->digital_signature;
        if ($path && Storage::disk('local')->exists($path)) {
            $fileData = Storage::disk('local')->get($path);
            $mimeType = Storage::disk('local')->mimeType($path);
            $this->currentSignature = 'data:' . $mimeType . ';base64,' . base64_encode($fileData);
        } else {
            $this->currentSignature = null;
        }
    }

    public function save()
    {
        $this->validate([
            'signature' => 'required|image|mimes:jpg,jpeg,png,webp|max:1024', // 1MB Max
        ]);

        $user = auth()->user();
        $barangayCode = $user->getActiveBarangayCode();

        if (!$barangayCode) {
            session()->flash('error', 'Unable to determine barangay code.');
            return;
        }

        $extension = $this->signature->getClientOriginalExtension();
        $filename = "{$user->id}.{$extension}";
        $directory = "barangay-assets/{$barangayCode}/signatures";

        // Ensure directory exists
        if (!Storage::disk('local')->exists($directory)) {
            Storage::disk('local')->makeDirectory($directory);
        }

        // Store the file in PRIVATE local disk
        $path = $this->signature->storeAs($directory, $filename, 'local');

        // Update user record
        $user->update([
            'digital_signature' => $path
        ]);

        $this->loadCurrentSignature();
        $this->signature = null;

        session()->flash('success', 'Digital signature updated successfully.');
    }

    public function render()
    {
        return view('livewire.officials.signature-upload');
    }
}

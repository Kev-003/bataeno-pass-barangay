<?php

namespace App\Livewire;

use Livewire\Component;

use Livewire\WithFileUploads;

class ProfilePhoto extends Component
{
    use WithFileUploads;

    public $photo;

    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'image|max:1024', // 1MB Max
        ]);

        $path = $this->photo->store('profile-photos', 'public');

        auth()->user()->update([
            'profile_photos' => $path,
        ]);

        session()->flash('photo_success', 'Profile photo updated!');
    }

    public function render()
    {
        return view('livewire.profile-photo');
    }
}

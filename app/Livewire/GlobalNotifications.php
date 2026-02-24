<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class GlobalNotifications extends Component
{
    public $notifications;
    public $unreadCount;

    public function mount()
    {
        $this->loadNotifications();
    }

    /**
     * Livewire 3 Dynamic Listeners
     * This allows us to use dynamic IDs for Echo channels.
     */
    public function getListeners()
    {
        $user = Auth::user();
        if (!$user)
            return ['notificationReceived' => '$refresh'];

        $userId = $user->id;
        $barangayCode = $user->getActiveBarangayCode();

        $listeners = [
            'notificationReceived' => '$refresh',
            "echo-private:resident.{$userId}.documents,DocumentIssued" => '$refresh',
            "echo-private:resident.{$userId}.documents,.DocumentIssued" => '$refresh',
        ];

        if ($barangayCode) {
            $listeners["echo-private:barangay.{$barangayCode}.requests,DocumentRequestCreated"] = '$refresh';
            $listeners["echo-private:barangay.{$barangayCode}.requests,.DocumentRequestCreated"] = '$refresh';
        }

        return $listeners;
    }

    public function loadNotifications()
    {
        $user = Auth::user();
        if (!$user)
            return;

        $this->notifications = $user->notifications()->take(10)->get();
        $this->unreadCount = $user->unreadNotifications()->count();
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
        $this->loadNotifications();
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function render()
    {
        $this->loadNotifications();
        return view('livewire.global-notifications');
    }
}

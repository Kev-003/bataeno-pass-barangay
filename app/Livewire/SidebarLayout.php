<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Url;

class SidebarLayout extends Component
{
    public $navItems = [];

    #[Url(as: 'tab')]
    public $activeTab;

    public function mount($navItems = [], $defaultTab = null)
    {
        $this->navItems = $navItems;

        // Set default active tab if not already set via URL
        if (!$this->activeTab && !empty($navItems)) {
            $this->activeTab = $defaultTab ?? array_key_first($navItems);
        }
    }

    public function setActiveTab($slug)
    {
        $this->activeTab = $slug;
    }

    public function render()
    {
        return view('livewire.sidebar-layout');
    }
}

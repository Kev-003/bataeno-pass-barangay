<?php

namespace App\Livewire\Officials;

use Livewire\Component;

class StatsCard extends Component
{
    public $title;
    public $value;
    public $color;
    public function render()
    {
        return view('livewire.officials.stats-card');
    }
}

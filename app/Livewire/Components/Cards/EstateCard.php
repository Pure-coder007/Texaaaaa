<?php

namespace App\Livewire\Components\Cards;

use App\Models\Estate;
use Livewire\Component;

class EstateCard extends Component
{
    public Estate $estate;

    public function mount(Estate $estate)
    {
        $this->estate = $estate;
    }

    public function render()
    {
        return view('livewire.components.cards.estate-card');
    }
}

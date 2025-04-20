<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Header extends Component
{
    public $mobileMenuOpen = false;

    public function render()
    {
        return view('livewire.components.header', [
            'user' => Auth::user(),
        ]);
    }

    public function toggleMobileMenu()
    {
        $this->mobileMenuOpen = !$this->mobileMenuOpen;
    }
}

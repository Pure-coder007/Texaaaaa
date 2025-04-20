<?php

namespace App\Livewire\Components;

use App\Models\City;
use App\Models\Estate;
use App\Models\State;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Footer extends Component
{
    public function render()
    {
        // Get popular states with estate counts
        $popularStates = State::select('states.id', 'states.name')
                              ->join('cities', 'states.id', '=', 'cities.state_id')
                              ->join('estates', 'cities.id', '=', 'estates.city_id')
                              ->where('estates.status', 'active')
                              ->groupBy('states.id', 'states.name')
                              ->orderByRaw('COUNT(estates.id) DESC')
                              ->take(5)
                              ->get();

        // Get random popular cities
        $popularCities = City::select('cities.id', 'cities.name', 'states.name as state_name')
                            ->join('states', 'cities.state_id', '=', 'states.id')
                            ->join('estates', 'cities.id', '=', 'estates.city_id')
                            ->where('estates.status', 'active')
                            ->groupBy('cities.id', 'cities.name', 'states.name')
                            ->orderByRaw('COUNT(estates.id) DESC')
                            ->take(5)
                            ->get();

        return view('livewire.components.footer', [
            'popularStates' => $popularStates,
            'popularCities' => $popularCities
        ]);
    }
}

<?php

namespace App\Livewire\Estates;

use App\Models\City;
use App\Models\Estate;
use App\Models\State;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class EstateListingPage extends Component
{
    use WithPagination;

    public $perPage = 9;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    #[Rule('nullable|string|max:255')]
    public $searchQuery = '';

    #[Rule('nullable|uuid')]
    public $state = '';

    #[Rule('nullable|uuid')]
    public $city = '';

    #[Rule('nullable|numeric|min:0')]
    public $minPrice;

    #[Rule('nullable|numeric|min:0')]
    public $maxPrice;

    public $states = [];
    public $cities = [];

    // For filtering
    public $showFilters = false;

    // Total count for display
    public $totalEstates = 0;

    public function mount()
    {
        // Initialize query params if they exist
        $this->searchQuery = request()->query('query', '');
        $this->state = request()->query('state', '');
        $this->city = request()->query('city', '');
        $this->minPrice = request()->query('minPrice', null);
        $this->maxPrice = request()->query('maxPrice', null);

        // Load states and cities for filters
        $this->loadStates();
        $this->loadCities();
    }

    public function loadStates()
    {
        // Get all states for dropdown
        $this->states = State::where('status', 'active')
            ->whereHas('cities', function($query) {
                $query->whereHas('estates', function($q) {
                    $q->where('status', 'active');
                });
            })
            ->orderBy('name')
            ->get();
    }

    public function loadCities()
    {
        $query = City::where('status', 'active')
            ->whereHas('estates', function($q) {
                $q->where('status', 'active');
            })
            ->orderBy('name');

        if ($this->state) {
            $query->where('state_id', $this->state);
        }

        $this->cities = $query->get();
    }

    public function updatedState()
    {
        $this->city = ''; // Reset city when state changes
        $this->loadCities();
        $this->resetPage(); // Reset pagination when filters change
    }

    public function updatedSearchQuery()
    {
        $this->resetPage();
    }

    public function updatedCity()
    {
        $this->resetPage();
    }

    public function updatedMinPrice()
    {
        $this->resetPage();
    }

    public function updatedMaxPrice()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function toggleShowFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function resetFilters()
    {
        $this->searchQuery = '';
        $this->state = '';
        $this->city = '';
        $this->minPrice = null;
        $this->maxPrice = null;
        $this->loadCities();
        $this->resetPage();
    }

    public function getEstatesProperty()
    {
        $query = Estate::with(['city', 'location', 'city.state', 'media', 'manager', 'plots'])
            ->where('status', 'active');

        // Apply search query if provided
        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('description', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('address', 'like', '%' . $this->searchQuery . '%');
            });
        }

        // Apply state filter if provided
        if ($this->state) {
            $query->whereHas('city', function ($q) {
                $q->where('state_id', $this->state);
            });
        }

        // Apply city filter if provided
        if ($this->city) {
            $query->where('city_id', $this->city);
        }

        // Apply price filters if provided
        // Note: This filters based on the minimum plot price in the estate
        if ($this->minPrice) {
            $query->whereHas('plots', function ($q) {
                $q->where('price', '>=', $this->minPrice);
            });
        }

        if ($this->maxPrice) {
            $query->whereHas('plots', function ($q) {
                $q->where('price', '<=', $this->maxPrice);
            });
        }

        // Sort the results
        $query->orderBy($this->sortField, $this->sortDirection);

        // Store total count for display
        $this->totalEstates = $query->count();

        // Return paginated results
        return $query->paginate($this->perPage);
    }

    public function render(): View
    {
        return view('livewire.estates.estate-listing-page', [
            'estates' => $this->estates,
        ]);
    }
}

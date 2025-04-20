<div>
    <!-- Page Header with search bar -->
    <div class="bg-gradient-to-r from-primary/90 to-primary-dark pb-8 pt-16">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-white mb-2">Browse Estates</h1>
                <p class="text-white/80">Find the perfect estate for your land investment</p>
            </div>

            <!-- Search Form -->
            <div class="bg-white rounded-xl p-4 shadow-lg" x-data="{ showFilters: @entangle('showFilters') }">
                <div class="flex flex-wrap lg:flex-nowrap items-center gap-3 mb-3">
                    <!-- Search input -->
                    <div class="relative flex-grow min-w-[200px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ph ph-magnifying-glass text-gray-400"></i>
                        </div>
                        <input wire:model.live.debounce.300ms="searchQuery" type="text"
                               class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                               placeholder="Search by estate name, location...">
                    </div>

                    <!-- Filters toggle button -->
                    <div>
                        <button type="button" wire:click="toggleShowFilters"
                                class="h-full px-4 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary">
                            <i class="ph ph-sliders"></i>
                            <span class="ml-1">Filters</span>
                            <span class="ml-1 bg-primary text-white text-xs rounded-full px-2 py-0.5">
                                {{ ($state || $city || $minPrice || $maxPrice) ? '!' : '' }}
                            </span>
                        </button>
                    </div>

                    <!-- Sort dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button"
                                class="flex items-center h-full px-4 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary">
                            <i class="ph ph-sort-ascending mr-1"></i>
                            <span class="ml-1">Sort</span>
                            <i class="ph ph-caret-down ml-1"></i>
                        </button>
                        <div x-show="open" @click.away="open = false"
                             class="absolute right-0 mt-2 w-48 bg-white shadow-xl rounded-xl py-1 z-10">
                            <button wire:click="sortBy('name')" class="w-full text-left px-4 py-2 hover:bg-gray-100">
                                Name {{ $sortField === 'name' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </button>
                            <button wire:click="sortBy('created_at')" class="w-full text-left px-4 py-2 hover:bg-gray-100">
                                Latest {{ $sortField === 'created_at' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </button>
                            <!-- Add more sort options as needed -->
                        </div>
                    </div>
                </div>

                <!-- Filters section (collapsible) -->
                <div x-show="showFilters" x-transition class="border-t border-gray-200 pt-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- State dropdown -->
                        <div>
                            <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State</label>
                            <select wire:model.live="state" id="state"
                                    class="block w-full py-2.5 px-3 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                                <option value="">All States</option>
                                @foreach($states as $stateOption)
                                    <option value="{{ $stateOption->id }}">{{ $stateOption->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- City dropdown -->
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <select wire:model.live="city" id="city"
                                    class="block w-full py-2.5 px-3 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary">
                                <option value="">All Cities</option>
                                @foreach($cities as $cityOption)
                                    <option value="{{ $cityOption->id }}">{{ $cityOption->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Min Price -->
                        <div>
                            <label for="min-price" class="block text-sm font-medium text-gray-700 mb-1">Min Price</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">₦</span>
                                </div>
                                <input wire:model.live.debounce.500ms="minPrice" type="number" min="0" id="min-price"
                                       class="block w-full pl-8 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                                       placeholder="Min">
                            </div>
                        </div>

                        <!-- Max Price -->
                        <div>
                            <label for="max-price" class="block text-sm font-medium text-gray-700 mb-1">Max Price</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">₦</span>
                                </div>
                                <input wire:model.live.debounce.500ms="maxPrice" type="number" min="0" id="max-price"
                                       class="block w-full pl-8 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                                       placeholder="Max">
                            </div>
                        </div>
                    </div>

                    <!-- Filter actions -->
                    <div class="flex justify-end mt-4">
                        <button wire:click="resetFilters" type="button"
                                class="text-gray-600 hover:text-primary mr-4">
                            <i class="ph ph-x mr-1"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estates Grid Section -->
    <div class="container mx-auto px-4 lg:px-8 py-12">
        <!-- Results count and display options -->
        <div class="flex flex-wrap justify-between items-center mb-8">
            <div class="text-gray-600 mb-4 md:mb-0">
                Showing <span class="font-medium">{{ $estates->count() }}</span> of
                <span class="font-medium">{{ $totalEstates }}</span> estates
            </div>

            <div class="flex gap-2 items-center">
                <label for="perPage" class="text-sm text-gray-600">Show:</label>
                <select wire:model.live="perPage" id="perPage"
                        class="border border-gray-300 rounded-lg text-sm py-1.5 px-2 focus:ring-primary focus:border-primary">
                    <option value="9">9</option>
                    <option value="18">18</option>
                    <option value="27">27</option>
                    <option value="36">36</option>
                </select>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div wire:loading.delay class="flex justify-center my-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
        </div>

        <!-- Estates Grid -->
        <div wire:loading.remove class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($estates as $estate)
                <livewire:components.cards.estate-card :estate="$estate" :key="$estate->id" />
            @empty
                <div class="col-span-3 py-12 text-center">
                    <div class="text-gray-400 text-5xl mb-4">
                        <i class="ph ph-buildings"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">No estates found</h3>
                    <p class="text-gray-500 mb-6">Try adjusting your search or filter criteria</p>
                    <button wire:click="resetFilters"
                            class="bg-primary hover:bg-primary-dark text-white px-5 py-2 rounded-lg transition-colors">
                        Reset Filters
                    </button>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $estates->links() }}
        </div>
    </div>
</div>

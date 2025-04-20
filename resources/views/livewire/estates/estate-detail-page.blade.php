<div>
    <!-- Breadcrumb -->
    <div class="bg-gray-100">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center text-sm text-gray-600">
                <a href="{{ route('home') }}" class="hover:text-primary">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('home') }}" class="hover:text-primary">Estates</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">{{ $estate->name }}</span>
            </div>
        </div>
    </div>

    <!-- Estate Header Section -->
    <div class="bg-white border-b">
        <div class="container mx-auto px-4 py-5">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ $estate->name }}</h1>
                    <div class="flex items-center mt-2 text-gray-600">
                        <i class="ph ph-map-pin text-primary"></i>
                        <span class="ml-2">{{ $estate->location->name }}, {{ $estate->city->name }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-3 md:mt-0">
                    <div class="text-right">
                        <p class="text-xs text-gray-600">Starting from</p>
                        <p class="text-lg font-bold text-primary">₦{{ number_format($plotStats['minPrice'] ?? 0) }}</p>
                    </div>

                    <button
                        wire:click="openPurchaseModal"
                        class="bg-primary hover:bg-primary-dark text-white px-5 py-2.5 rounded-lg transition-colors flex items-center">
                        <i class="ph ph-shopping-cart mr-2"></i>
                        Buy Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-6">
        <!-- Tabs Navigation -->
        <div class="mb-6 border-b overflow-x-auto scrollbar-hide">
            <div class="flex whitespace-nowrap -mb-px">
                <button wire:click="changeTab('plots')" class="py-3 px-5 font-medium text-sm border-b-2 {{ $tab === 'plots' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} transition-colors">
                    Available Plots
                </button>
                <button wire:click="changeTab('features')" class="py-3 px-5 font-medium text-sm border-b-2 {{ $tab === 'features' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} transition-colors">
                    Features
                </button>
                <button wire:click="changeTab('faq')" class="py-3 px-5 font-medium text-sm border-b-2 {{ $tab === 'faq' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} transition-colors">
                    FAQ
                </button>
                <button wire:click="changeTab('terms')" class="py-3 px-5 font-medium text-sm border-b-2 {{ $tab === 'terms' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} transition-colors">
                    Terms
                </button>
                <button wire:click="changeTab('refund')" class="py-3 px-5 font-medium text-sm border-b-2 {{ $tab === 'refund' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} transition-colors">
                    Refund Policy
                </button>
            </div>
        </div>

        <!-- Available Plots Tab -->
        @if($tab === 'plots')
            <div class="mb-6">
               <!-- Selected Plots Summary (Mobile) -->
                @if($this->getSelectedCount() > 0)
                <div class="md:hidden mb-4 bg-white rounded-lg shadow-md p-4">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-bold text-gray-900">Selected Plots: {{ $this->getSelectedCount() }}</h3>
                        <span class="text-primary font-bold">₦{{ number_format($totalPrice) }}</span>
                    </div>
                    <button
                        wire:click="openPurchaseModal"
                        class="w-full bg-primary hover:bg-primary-dark text-white py-2.5 rounded-lg transition-colors flex items-center justify-center">
                        <i class="ph ph-shopping-cart mr-2"></i>
                        Proceed to Payment
                    </button>
                </div>
            @endif

                <!-- Mobile Filters (Collapsible) -->
                {{-- <div x-data="{ open: false }" class="md:hidden mb-4">
                    <button
                        @click="open = !open"
                        class="w-full flex justify-between items-center bg-white p-3 rounded-lg shadow-sm"
                    >
                        <span class="font-medium">Filter Plots</span>
                        <i class="ph" :class="open ? 'ph-caret-up' : 'ph-caret-down'"></i>
                    </button>

                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                        class="bg-white rounded-lg shadow-md p-4 mt-2 space-y-4"
                    >
                        <!-- Plot Type Filter -->
                        @if(count($plotTypes) > 0)
                            <div>
                                <label for="mobile-plot-type-filter" class="block text-sm font-medium text-gray-700 mb-1">
                                    Plot Type
                                </label>
                                <select id="mobile-plot-type-filter" wire:model.live="plotTypeFilter" class="w-full border py-2 px-4 border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring-primary text-sm">
                                    <option value="">All Types</option>
                                    @foreach($plotTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }} ({{ number_format($type->size_sqm) }} sqm)</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Plot Size Filter -->
                        <div>
                            <label for="mobile-size-filter" class="block text-sm font-medium text-gray-700 mb-1">
                                Plot Size
                            </label>
                            <select id="mobile-size-filter" wire:model.live="sizeFilter" class="w-full border py-2 px-4 border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring-primary text-sm">
                                <option value="">All Sizes</option>
                                @foreach($availablePlotSizes as $size)
                                    <option value="{{ $size }}">{{ number_format($size) }} sqm</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Plot Attributes Filters -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Plot Attributes
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model.live="commercialFilter" class="rounded text-primary focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">Commercial Plots</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model.live="cornerFilter" class="rounded text-primary focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">Corner Plots</span>
                                </label>
                            </div>
                        </div>



                        <!-- Clear Filters Button -->
                        <div>
                            <button type="button" wire:click="resetFilters" class="w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                Clear All Filters
                            </button>
                        </div>
                    </div>
                </div> --}}

                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Sidebar Filters (Desktop) -->
                    @if($this->getSelectedCount() > 0)
                    <div class="hidden md:block md:w-1/4">
                        <div class="bg-white rounded-lg shadow-md p-4 sticky top-4">
                            {{-- <h3 class="font-bold text-gray-900 mb-4">Filter Plots</h3>

                            <!-- Plot Type Filter -->
                            @if(count($plotTypes) > 0)
                                <div class="mb-4">
                                    <label for="plot-type-filter" class="block text-sm font-medium text-gray-700 mb-1">
                                        Plot Type
                                    </label>
                                    <select id="plot-type-filter" wire:model.live="plotTypeFilter" class="w-full border py-2 px-4 border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring-primary text-sm">
                                        <option value="">All Types</option>
                                        @foreach($plotTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }} ({{ number_format($type->size_sqm) }} sqm)</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <!-- Plot Size Filter -->
                            <div class="mb-4">
                                <label for="size-filter" class="block text-sm font-medium text-gray-700 mb-1">
                                    Plot Size
                                </label>
                                <select id="size-filter" wire:model.live="sizeFilter" class="w-full border py-2 px-4 border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring-primary text-sm">
                                    <option value="">All Sizes</option>
                                    @foreach($availablePlotSizes as $size)
                                        <option value="{{ $size }}">{{ number_format($size) }} sqm</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Plot Attributes Filters -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Plot Attributes
                                </label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model.live="commercialFilter" class="rounded text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700">Commercial Plots</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model.live="cornerFilter" class="rounded text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700">Corner Plots</span>
                                    </label>
                                </div>
                            </div>



                            <!-- Clear Filters Button -->
                            <div class="mb-6">
                                <button type="button" wire:click="resetFilters" class="w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                    Clear All Filters
                                </button>
                            </div> --}}

                            <!-- Purchase Summary (Desktop) -->
                            @if($this->getSelectedCount() > 0)
                            <div class="pt-4 border-t border-gray-200">
                                <h3 class="font-bold text-gray-900 mb-2">Purchase Summary</h3>
                                <div class="text-sm">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-gray-600">Selected Plots:</span>
                                        <span class="font-medium">{{ $this->getSelectedCount() }}</span>
                                    </div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-gray-600">Total Area:</span>
                                        <span class="font-medium">{{ number_format($totalArea) }} sqm</span>
                                    </div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-gray-600">Base Price:</span>
                                        <span class="font-medium">₦{{ number_format($basePrice) }}</span>
                                    </div>
                                    @if($commercialPremium > 0)
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-gray-600">Commercial Plot Additional Fee:</span>
                                            <span class="font-medium">₦{{ number_format($commercialPremium) }}</span>
                                        </div>
                                    @endif

                                    @if($cornerPremium > 0)
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-gray-600">Corner Plot Additional Fee:</span>
                                            <span class="font-medium">₦{{ number_format($cornerPremium) }}</span>
                                        </div>
                                    @endif
                                    @if($freePlots > 0)
                                        <div class="flex justify-between items-center mb-1 text-green-600">
                                            <span>Bonus Plots:</span>
                                            <span class="font-medium">{{ $freePlots }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between items-center pt-2 mt-2 border-t border-gray-200 font-bold">
                                        <span>Total Price:</span>
                                        <span class="text-primary">₦{{ number_format($totalPrice) }}</span>
                                    </div>
                                </div>

                                <button
                                    wire:click="openPurchaseModal"
                                    class="w-full mt-4 bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg transition-colors flex justify-center items-center">
                                    <i class="ph ph-shopping-cart mr-2"></i>
                                    Proceed to Payment
                                </button>
                            </div>
                        @endif
                        </div>
                    </div>
                    @endif

                    <!-- Plots Grid/List -->
                    <div class="md:w-3/4">
                        @if(count($this->groupedPlots) > 0)
                    <div class="space-y-6">
                        <!-- Group by category first -->
                        @php
                            $categorizedGroups = [];
                            foreach($this->groupedPlots as $key => $group) {
                                $category = $group['category'];
                                if (!isset($categorizedGroups[$category])) {
                                    $categorizedGroups[$category] = [];
                                }
                                $categorizedGroups[$category][] = $group;
                            }
                        @endphp

                        @foreach($categorizedGroups as $category => $groups)
                            <div class="bg-white rounded-lg shadow-md overflow-hidden p-4">
                                <h3 class="text-lg font-bold text-gray-900 mb-3">{{ $category }} Plots</h3>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4">
                                    @foreach($groups as $group)
                                        @php $key = "{$group['category']}_{$group['size']}_{$group['plot_type_id']}"; @endphp
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <div class="flex justify-between items-center mb-2">
                                                <h4 class="font-semibold text-gray-800">{{ number_format($group['size']) }} sqm</h4>
                                                <div class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                                    {{ $group['count'] }} Available
                                                </div>
                                            </div>

                                            <div class="space-y-1 text-sm mb-3">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-600">Outright Price:</span>
                                                    <span class="font-medium">₦{{ number_format($group['outright_price']) }}</span>
                                                </div>

                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-600">6 Months:</span>
                                                    <span class="font-medium">₦{{ number_format($group['six_month_price']) }}</span>
                                                </div>

                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-600">12 Months:</span>
                                                    <span class="font-medium">₦{{ number_format($group['twelve_month_price']) }}</span>
                                                </div>
                                            </div>

                                            <div class="flex justify-between items-center mt-4">
                                                <span class="font-medium text-gray-700">Quantity:</span>
                                                <div class="flex items-center space-x-3">
                                                    <button
                                                        wire:click="decrementQuantity('{{ $key }}')"
                                                        class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center {{ isset($selectedQuantities[$key]) && $selectedQuantities[$key] > 0 ? 'text-red-600 hover:bg-red-50' : 'text-gray-400 cursor-not-allowed' }}">
                                                        <i class="ph ph-minus"></i>
                                                    </button>

                                                    <span class="font-semibold">{{ $selectedQuantities[$key] ?? 0 }}</span>

                                                    <button
                                                        wire:click="incrementQuantity('{{ $key }}')"
                                                        class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center {{ ($selectedQuantities[$key] ?? 0) < $group['count'] ? 'text-green-600 hover:bg-green-50' : 'text-gray-400 cursor-not-allowed' }}">
                                                        <i class="ph ph-plus"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            @if(($selectedQuantities[$key] ?? 0) > 0)
                                                <div class="mt-3 text-right">
                                                    <span class="font-medium text-primary">
                                                        Selected: ₦{{ number_format(($selectedQuantities[$key] ?? 0) * $group['outright_price']) }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white rounded-lg shadow-md p-6 text-center">
                        <i class="ph ph-warning-circle text-4xl text-yellow-500 mb-2"></i>
                        <h3 class="text-xl font-bold text-gray-900 mb-1">No Plots Available</h3>
                        <p class="text-gray-600">There are currently no plots available in this estate.</p>
                    </div>
                @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Features Tab Content -->
        @if($tab === 'features')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Gallery -->
                <div class="md:col-span-2">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                        <div class="relative aspect-video">
                            @if($activePhoto)
                                <img src="{{ $activePhoto->getUrl() }}"
                                    alt="{{ $estate->name }}"
                                    class="w-full h-full object-cover"
                                    x-data
                                    x-on:click="$dispatch('img-modal', {imgModalSrc: '{{ $activePhoto->getUrl() }}', imgModalDesc: '{{ $estate->name }}'})"
                                >
                            @else
                                <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                    <i class="ph ph-buildings text-4xl text-gray-400"></i>
                                </div>
                            @endif
                        </div>

                        {{-- @if(count($galleryPhotos) > 1)
                            <div class="p-3 flex space-x-2 overflow-x-auto">
                                @foreach($galleryPhotos as $index => $photo)
                                    <div wire:click="changeActivePhoto({{ $index }})"
                                        class="w-16 h-16 md:w-20 md:h-20 flex-shrink-0 rounded overflow-hidden cursor-pointer {{ $activePhoto->id === $photo->id ? 'ring-2 ring-primary' : '' }}">
                                        <img src="{{ $photo->getUrl('thumb') }}"
                                            alt="{{ $estate->name }} - Photo {{ $index + 1 }}"
                                            class="w-full h-full object-cover">
                                    </div>
                                @endforeach
                            </div>
                        @endif --}}
                    </div>

                    <!-- Description -->
                    <div class="bg-white rounded-lg shadow-md p-5 mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-3">Description</h2>
                        <div class="prose max-w-none">
                            <p class="text-gray-700">{{ $estate->description }}</p>
                        </div>
                    </div>

                    <!-- Key Information -->
                    <div class="bg-white rounded-lg shadow-md p-5 mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-3">Key Information</h2>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div class="flex flex-col">
                                <span class="text-gray-500 text-xs">Total Area</span>
                                <span class="font-semibold text-gray-900">{{ number_format($estate->total_area) }} sqm</span>
                            </div>

                            <div class="flex flex-col">
                                <span class="text-gray-500 text-xs">Total Plots</span>
                                <span class="font-semibold text-gray-900">{{ $plotStats['total'] ?? 0 }}</span>
                            </div>

                            <div class="flex flex-col">
                                <span class="text-gray-500 text-xs">Available Plots</span>
                                <span class="font-semibold text-green-600">{{ $plotStats['available'] ?? 0 }}</span>
                            </div>

                            <div class="flex flex-col">
                                <span class="text-gray-500 text-xs">Sold Plots</span>
                                <span class="font-semibold text-gray-900">{{ $plotStats['sold'] ?? 0 }}</span>
                            </div>

                            <div class="flex flex-col">
                                <span class="text-gray-500 text-xs">Price Range</span>
                                <span class="font-semibold text-gray-900">₦{{ number_format($plotStats['minPrice'] ?? 0) }} - ₦{{ number_format($plotStats['maxPrice'] ?? 0) }}</span>
                            </div>

                            <div class="flex flex-col">
                                <span class="text-gray-500 text-xs">Plot Sizes</span>
                                <span class="font-semibold text-gray-900">{{ number_format($plotStats['minArea'] ?? 0) }} - {{ number_format($plotStats['maxArea'] ?? 0) }} sqm</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="md:col-span-1">
                    <!-- Plot Types -->
                    @if(count($plotTypes) > 0)
                        <div class="bg-white rounded-lg shadow-md p-5 mb-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-3">Plot Types & Pricing</h2>
                            <div class="space-y-3">
                                @foreach($plotTypes as $type)
                                    <div class="border border-gray-200 rounded p-3">
                                        <h3 class="font-semibold text-gray-900">{{ $type->name }}</h3>
                                        <p class="text-xs text-gray-600 mb-2">{{ number_format($type->size_sqm) }} sqm</p>

                                        <div class="space-y-1 text-sm">
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-600">Outright:</span>
                                                <span class="font-medium">₦{{ number_format($type->outright_price) }}</span>
                                            </div>

                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-600">6 Months:</span>
                                                <span class="font-medium">₦{{ number_format($type->six_month_price) }}</span>
                                            </div>

                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-600">12 Months:</span>
                                                <span class="font-medium">₦{{ number_format($type->twelve_month_price) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Active Promotions -->
                    @if(count($activePromos) > 0)
                        <div class="bg-white rounded-lg shadow-md p-5 mb-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-3">Active Promotions</h2>
                            <div class="space-y-3">
                                @foreach($activePromos as $promo)
                                    <div class="border border-green-200 bg-green-50 rounded p-3">
                                        <h3 class="font-semibold text-gray-900">{{ $promo->name }}</h3>
                                        <p class="text-xs text-gray-600 mb-2">{{ $promo->description }}</p>

                                        <div class="bg-white border border-green-100 rounded p-2 text-center my-2">
                                            <span class="text-sm font-bold text-green-600">Buy {{ $promo->buy_quantity }}</span>
                                            <i class="ph ph-arrow-right mx-1 text-gray-400"></i>
                                            <span class="text-sm font-bold text-green-600">Get {{ $promo->free_quantity }} Free</span>
                                        </div>

                                        @if($promo->valid_to)
                                            <div class="text-xs text-gray-500">
                                                Valid until: {{ $promo->valid_to->format('d M, Y') }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Document Verification -->
                    <div class="bg-white rounded-lg shadow-md p-5 mb-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-3">Document Verification</h2>

                        <div class="space-y-2">
                            <div class="flex items-center text-green-600">
                                <i class="ph ph-check-circle text-lg mr-2"></i>
                                <span class="text-sm">Land Title Verified</span>
                            </div>

                            <div class="flex items-center text-green-600">
                                <i class="ph ph-check-circle text-lg mr-2"></i>
                                <span class="text-sm">Survey Plan Available</span>
                            </div>

                            <div class="flex items-center text-green-600">
                                <i class="ph ph-check-circle text-lg mr-2"></i>
                                <span class="text-sm">Deed of Assignment</span>
                            </div>

                            <div class="flex items-center text-green-600">
                                <i class="ph ph-check-circle text-lg mr-2"></i>
                                <span class="text-sm">Layout Approval</span>
                            </div>
                        </div>

                        <p class="text-xs text-gray-500 mt-3">
                            All our estates have verified documentation. Upon purchase, you'll receive all necessary legal documents.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- FAQ Tab -->
        @if($tab === 'faq')
            <div class="bg-white rounded-lg shadow-md p-5 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h2>

                @if($estate->faq && is_array($estate->faq) && count($estate->faq) > 0)
                    <div class="space-y-4">
                        @foreach($estate->faq as $index => $faq)
                            <div x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }" class="border border-gray-200 rounded-lg overflow-hidden">
                                <button
                                    x-on:click="open = !open"
                                    class="flex justify-between items-center w-full px-4 py-3 text-left font-medium text-gray-900 hover:bg-gray-50 focus:outline-none"
                                >
                                    <span class="text-sm">{{ $faq['question'] }}</span>
                                    <i class="ph" :class="open ? 'ph-minus' : 'ph-plus'"></i>
                                </button>

                                <div
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-2"
                                    class="px-4 pb-3 text-sm text-gray-600"
                                >
                                    <p>{{ $faq['answer'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <i class="ph ph-info text-4xl text-gray-400 mb-2"></i>
                        <p class="text-gray-600">FAQs for this estate will be available soon.</p>
                    </div>
                @endif
            </div>
        @endif

        <!-- Terms & Conditions Tab -->
        @if($tab === 'terms')
            <div class="bg-white rounded-lg shadow-md p-5 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Terms & Conditions</h2>

                @if($estate->terms && is_array($estate->terms) && count($estate->terms) > 0)
                    <div class="space-y-4">
                        @foreach($estate->terms as $term)
                        <div class="mb-4">
                            <h3 class="font-semibold text-gray-900 mb-2">{{ $term['title'] }}</h3>
                            <p class="text-sm text-gray-600">{{ $term['content'] }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6">
                    <i class="ph ph-file-text text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-600">Terms and conditions for this estate will be available soon.</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Refund Policy Tab -->
    @if($tab === 'refund')
        <div class="bg-white rounded-lg shadow-md p-5 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Refund Policy</h2>

            @if($estate->refund_policy && is_array($estate->refund_policy) && count($estate->refund_policy) > 0)
                <div class="space-y-4">
                    @foreach($estate->refund_policy as $policy)
                        <div class="mb-4">
                            <h3 class="font-semibold text-gray-900 mb-2">{{ $policy['title'] }}</h3>
                            <p class="text-sm text-gray-600">{{ $policy['content'] }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6">
                    <i class="ph ph-receipt text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-600">Refund policy for this estate will be available soon.</p>
                </div>
            @endif
        </div>
    @endif
</div>


<!-- Floating Action Button (Mobile) -->
@if(count($selectedPlots) > 0)
<div class="md:hidden fixed bottom-4 right-4 z-10">
    <button
        wire:click="openPurchaseModal"
        class="bg-primary hover:bg-primary-dark text-white h-16 w-16 rounded-full shadow-lg flex items-center justify-center"
    >
        <div class="relative">
            <i class="ph ph-shopping-cart text-2xl"></i>
            <span class="absolute -top-2 -right-2 bg-white text-primary text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                {{ count($selectedPlots) }}
            </span>
        </div>
    </button>
</div>
@endif

<!-- Purchase Modal -->
<!-- Updated Purchase Modal -->
<div x-data="{ show: @entangle('showPurchaseModal') }"
 x-show="show"
 x-transition:enter="transition ease-out duration-300"
 x-transition:enter-start="opacity-0"
 x-transition:enter-end="opacity-100"
 x-transition:leave="transition ease-in duration-200"
 x-transition:leave-start="opacity-100"
 x-transition:leave-end="opacity-0"
 class="fixed inset-0 z-50 overflow-y-auto"
 style="display: none;">

<div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
    <!-- Background overlay -->
    <div x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 transition-opacity"
         aria-hidden="true">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

    <!-- Modal panel -->
    <div x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

        <!-- Modal Header -->
        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                Complete Your Purchase
            </h3>
        </div>

        <div class="bg-white px-4 pt-4 pb-4 sm:p-6 sm:pb-4">
            <!-- Premium Pricing Information -->
            <div class="mb-4 bg-blue-50 p-3 rounded-lg border border-blue-100">
                <h4 class="font-medium text-blue-800 mb-1">Plot Pricing Information</h4>

                <div class="text-sm text-blue-700 space-y-1">
                    @if($commercialPremium > 0)
                        <p>
                            <span class="font-medium">Commercial Plots:</span> +{{ $estate->commercial_plot_premium_percentage }}% premium.
                            Selected: ₦{{ number_format($commercialPremium) }}
                        </p>
                    @else
                        <p>
                            <span class="font-medium">Commercial Plots:</span> +{{ $estate->commercial_plot_premium_percentage }}% premium
                        </p>
                    @endif

                    @if($cornerPremium > 0)
                        <p>
                            <span class="font-medium">Corner Plots:</span> +{{ $estate->corner_plot_premium_percentage }}% premium.
                            Selected: ₦{{ number_format($cornerPremium) }}
                        </p>
                    @else
                        <p>
                            <span class="font-medium">Corner Plots:</span> +{{ $estate->corner_plot_premium_percentage }}% premium
                        </p>
                    @endif

                    <p class="text-xs pt-1">
                        <i class="ph ph-info mr-1"></i>
                        Premiums are automatically calculated and added to the total price for any commercial or corner plots you select.
                    </p>
                </div>
            </div>

            <!-- Selected Plots Summary -->
            <div class="mb-5 bg-gray-50 p-3 rounded-lg">
                <div class="flex justify-between items-center text-sm mb-1">
                    <span class="text-gray-600">Estate:</span>
                    <span class="font-medium">{{ $estate->name }}</span>
                </div>
                <div class="flex justify-between items-center text-sm mb-1">
                    <span class="text-gray-600">Selected Plots:</span>
                    <span class="font-medium">{{ count($selectedPlots) }}</span>
                </div>
                <div class="flex justify-between items-center text-sm mb-1">
                    <span class="text-gray-600">Total Area:</span>
                    <span class="font-medium">{{ number_format($totalArea) }} sqm</span>
                </div>

                <div class="flex justify-between items-center text-sm mb-1">
                    <span class="text-gray-600">Base Price:</span>
                    <span class="font-medium">₦{{ number_format($basePrice) }}</span>
                </div>

                @if($commercialPremium > 0)
                    <div class="flex justify-between items-center text-sm mb-1">
                        <span class="text-gray-600">Commercial Plot Additional Fee:</span>
                        <span class="font-medium">₦{{ number_format($commercialPremium) }}</span>
                    </div>
                @endif

                @if($cornerPremium > 0)
                    <div class="flex justify-between items-center text-sm mb-1">
                        <span class="text-gray-600">Corner Plot Additional Fee:</span>
                        <span class="font-medium">₦{{ number_format($cornerPremium) }}</span>
                    </div>
                @endif

                @if($discountAmount > 0)
                    <div class="flex justify-between items-center text-sm mb-1 text-green-600">
                        <span>Discount:</span>
                        <span class="font-medium">-₦{{ number_format($discountAmount) }}</span>
                    </div>
                @endif

                <div class="flex justify-between items-center font-medium pt-2 border-t border-gray-200">
                    <span class="text-gray-900">Total Price:</span>
                    <span class="text-primary">₦{{ number_format($totalPrice) }}</span>
                </div>
            </div>

            <form wire:submit.prevent="proceedToPayment" class="space-y-4">
                <!-- Agent Code -->
                <div>
                    <label for="agent-code" class="block text-sm font-medium text-gray-700 mb-1">
                        PBO Code (Required)
                    </label>
                    <div class="flex">
                        <input type="text"
                            id="agent-code"
                            wire:model="agentCode"
                            class="w-full py-2.5 px-3 border border-gray-300 rounded-lg focus:ring-primary focus:border-primary"
                            placeholder="Enter PBO's code">
                        <button type="button"
                            wire:click="validateAgentCode"
                            class="ml-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                            Verify
                        </button>
                    </div>
                    @if($agentCodeMessage)
                        <p class="mt-1 text-sm {{ $agentCodeValid ? 'text-green-600' : 'text-red-600' }}">
                            {{ $agentCodeMessage }}
                        </p>
                    @else
                        <p class="mt-1 text-xs text-gray-500">
                            Enter your PBO's referral code. This is required to complete your purchase.
                        </p>
                    @endif
                </div>

                <!-- How did you hear about us field -->
                <div>
                    <label for="referral-source" class="block text-sm font-medium text-gray-700 mb-1">
                        How did you hear about us?
                    </label>
                    <select
                        id="referral-source"
                        wire:model.live="referralSource"
                       class="w-full py-2.5 px-3 border {{ $errors->has('referralSource') ? 'border-red-300' : 'border-gray-300' }} rounded-lg focus:ring-primary focus:border-primary"
                    >
                        <option value="">-- Please select --</option>
                        <option value="website">Website</option>
                        <option value="tv_radio">TV/Radio</option>
                        <option value="referral">Referral</option>
                        <option value="social_media">Social Media</option>
                        <option value="outdoor_ads">Outdoor Ads/Billboards</option>
                        <option value="others">Others</option>
                    </select>
                    @error('referralSource')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payment Plan Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Payment Plan
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Outright Payment -->
                        <label class="relative border rounded-lg p-3 cursor-pointer hover:border-primary transition-colors {{ $paymentPlanType === 'outright' ? 'border-primary bg-primary/5' : 'border-gray-200' }}">
                            <div class="flex items-start">
                                <input type="radio"
                                    wire:model.live="paymentPlanType"
                                    value="outright"
                                    class="h-4 w-4 text-primary border-gray-300 focus:ring-primary">

                                <div class="ml-2">
                                    <span class="block text-sm font-medium text-gray-900">Outright</span>
                                    <span class="block text-xs text-gray-500">(0-3 months)</span>
                                    @if($activePromos->count() > 0)
                                        <span class="block text-xs text-green-600 mt-1">Eligible for promotions</span>
                                    @endif
                                </div>
                            </div>
                        </label>

                        <!-- 6 Months Installment -->
                        <label class="relative border rounded-lg p-3 cursor-pointer hover:border-primary transition-colors {{ $paymentPlanType === '6_months' ? 'border-primary bg-primary/5' : 'border-gray-200' }}">
                            <div class="flex items-start">
                                <input type="radio"
                                    wire:model.live="paymentPlanType"
                                    value="6_months"
                                    class="h-4 w-4 text-primary border-gray-300 focus:ring-primary">

                                <div class="ml-2">
                                    <span class="block text-sm font-medium text-gray-900">6 Months</span>
                                    <span class="block text-xs text-gray-500">Installment</span>
                                </div>
                            </div>
                        </label>

                        <!-- 12 Months Installment -->
                        <label class="relative border rounded-lg p-3 cursor-pointer hover:border-primary transition-colors {{ $paymentPlanType === '12_months' ? 'border-primary bg-primary/5' : 'border-gray-200' }}">
                            <div class="flex items-start">
                                <input type="radio"
                                    wire:model.live="paymentPlanType"
                                    value="12_months"
                                    class="h-4 w-4 text-primary border-gray-300 focus:ring-primary">

                                <div class="ml-2">
                                    <span class="block text-sm font-medium text-gray-900">12 Months</span>
                                    <span class="block text-xs text-gray-500">Installment</span>
                                </div>
                            </div>
                        </label>
                    </div>
                    @if($paymentPlanType !== 'outright' && $activePromos->count() > 0)
                        <div class="mt-2 text-xs text-amber-600">
                            <i class="ph ph-info-circle mr-1"></i>
                            Promotions are only available for outright payments.
                        </div>
                    @endif
                </div>

                <!-- Initial Payment Input (for installment plans) -->
                @if($paymentPlanType !== 'outright')
                    <div>
                        <label for="initial-payment" class="block text-sm font-medium text-gray-700 mb-1">
                            Initial Payment (min. {{ $minInitialPaymentPercentage }}%)
                        </label>
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500">₦</span>
                            </div>
                            <input type="number"
                                id="initial-payment"
                                wire:model.lazy.live="initialPayment"
                                min="{{ $minInitialPaymentAmount }}"
                                max="{{ $totalPrice }}"
                                value="{{ $minInitialPaymentAmount }}"
                                class="block border w-full pl-8 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary sm:text-sm"
                                placeholder="{{ number_format($minInitialPaymentAmount) }}">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Minimum initial payment: ₦{{ number_format($minInitialPaymentAmount) }} ({{ $minInitialPaymentPercentage }}%)
                            </p>
                            @error('initialPayment')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="flex justify-between items-center mt-2 text-sm">
                                <span class="text-gray-600">Remaining Amount:</span>
                                <span class="font-medium">₦{{ number_format($totalWithFeesAndTax - $initialPayment) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Duration:</span>
                                <span class="font-medium">{{ $paymentPlanType === '6_months' ? '6' : '12' }} months</span>
                            </div>
                        </div>
                    @endif

                    <!-- Auto-applied Promo Information (for buy X get Y free) -->
                    @if($autoAppliedPromo)
                        <div class="bg-green-50 border border-green-200 rounded p-3 text-sm text-green-700">
                            <h4 class="font-bold mb-1">Promotional Offer Automatically Applied!</h4>
                            <p>You qualify for {{ $freePlots }} free plot(s) with your purchase of {{ count($selectedPlots) }} plots!</p>
                            <p class="mt-1 text-xs">{{ $autoAppliedPromo->name }}: Buy {{ $autoAppliedPromo->buy_quantity }} plots, get {{ $autoAppliedPromo->free_quantity }} free.</p>
                        </div>
                    @endif

                    <!-- Promo Code for discount -->
                    <div>
                        <label for="promo-code" class="block text-sm font-medium text-gray-700 mb-1">
                            Promo Code (Optional)
                        </label>
                        <div class="flex">
                            <input type="text"
                                id="promo-code"
                                wire:model.defer="promoCode"
                                class="flex-1 py-2.5 px-3 border border-gray-300 rounded-l-lg focus:ring-primary focus:border-primary"
                                placeholder="Enter discount code if you have one">
                            <button type="button"
                                class="bg-primary hover:bg-primary-dark text-white px-4 py-2.5 rounded-r-lg transition-colors"
                                wire:click="validatePromoCode">
                                Apply
                            </button>
                        </div>
                        @if($promoCodeMessage)
                            <p class="mt-1 text-sm {{ $promoCodeValid ? 'text-green-600' : 'text-red-600' }}">
                                {{ $promoCodeMessage }}
                            </p>
                        @else
                            <p class="mt-1 text-xs text-gray-500">
                                Enter a valid promo code to get a discount on your purchase.
                            </p>
                        @endif
                    </div>

                    <!-- Selected Plots List -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Selected Plots
                        </label>
                        <div class="max-h-40 overflow-y-auto bg-gray-50 rounded p-2">
                            @if($this->getSelectedCount() > 0)
                                <div class="space-y-2">
                                    @foreach($this->selectedQuantities as $key => $quantity)
                                        @if($quantity > 0 && isset($this->groupedPlots[$key]))
                                            @php $group = $this->groupedPlots[$key]; @endphp
                                            <div class="flex justify-between items-center p-2 rounded bg-white shadow-sm text-sm">
                                                <div>
                                                    <span class="font-medium">{{ $group['category'] }} ({{ number_format($group['size']) }} sqm)</span>
                                                    <span class="ml-2 text-primary font-medium">x{{ $quantity }}</span>
                                                </div>
                                                <span class="text-gray-700">₦{{ number_format($quantity * $group['outright_price']) }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 text-gray-500">
                                    No plots selected
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Free Plots Information -->
                    @if($freePlots > 0)
                        <div class="bg-green-50 border border-green-200 rounded p-3 text-sm text-green-700">
                            <h4 class="font-bold mb-1">Promotional Offer Applied!</h4>
                            <p>Congratulations! You qualify for {{ $freePlots }} free plot(s) with your purchase.</p>
                            <p class="mt-1 text-xs">These plots will be allocated to you after your purchase is completed. Our team will contact you to select your free plot(s).</p>
                        </div>
                    @endif

                    <!-- Payment breakdown including tax and fees -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Payment Breakdown</h4>
                        <div class="bg-gray-50 rounded p-3">
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-medium">₦{{ number_format($totalPrice) }}</span>
                                </div>

                                @if($taxAmount > 0)
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-gray-600">VAT (7.5%):</span>
                                        <span class="font-medium">₦{{ number_format($taxAmount) }}</span>
                                    </div>
                                @endif

                                @if($processingFee > 0)
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-gray-600">Processing Fee:</span>
                                        <span class="font-medium">₦{{ number_format($processingFee) }}</span>
                                    </div>
                                @endif

                                <div class="flex justify-between items-center pt-2 mt-1 border-t border-gray-200 font-bold">
                                    <span>Total Due:</span>
                                    <span class="text-primary">₦{{ number_format($totalWithFeesAndTax) }}</span>
                                </div>

                                @if($paymentPlanType !== 'outright')
                                    <div class="flex justify-between items-center mt-1 font-medium">
                                        <span>Initial Payment:</span>
                                        <span>₦{{ number_format($initialPayment) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center mt-0.5 text-gray-600">
                                        <span>Balance After Initial Payment:</span>
                                        <span>₦{{ number_format($totalWithFeesAndTax - $initialPayment) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Flexible Payment Information -->
                    @if($paymentPlanType !== 'outright')
                        <div class="bg-blue-50 border border-blue-200 rounded p-3 text-sm text-blue-700">
                            <h4 class="font-bold mb-1">Flexible Payment System</h4>
                            <p>With our flexible installment system, you can pay any amount at any time within your selected duration.</p>
                            <ul class="list-disc list-inside mt-2 space-y-1 text-xs">
                                <li>Initial payment: Minimum {{ $minInitialPaymentPercentage }}% of total price</li>
                                <li>Pay any amount anytime</li>
                                <li>No fixed monthly payments</li>
                                <li>Complete payment within your selected timeframe</li>
                            </ul>
                        </div>
                    @endif

                    <div class="mt-5 sm:mt-4 flex flex-col sm:flex-row-reverse gap-3">
                        <button type="submit"
                            class="w-full sm:w-auto flex justify-center items-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            <i class="ph ph-credit-card mr-2"></i>
                            Proceed to Payment
                        </button>
                        <button type="button"
                            wire:click="closePurchaseModal"
                            class="w-full sm:w-auto flex justify-center items-center py-2.5 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal for Gallery -->
<div x-data="{ imgModal : false, imgModalSrc : '', imgModalDesc : '' }"
     x-init="
        $watch('imgModal', value => {
            if (value) {
                document.body.classList.add('overflow-y-hidden');
            } else {
                document.body.classList.remove('overflow-y-hidden');
            }
        });
     "
     @img-modal.window="imgModal = true; imgModalSrc = $event.detail.imgModalSrc; imgModalDesc = $event.detail.imgModalDesc;"
     x-cloak>

    <div x-show="imgModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-on:click.away="imgModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center w-full h-full bg-black bg-opacity-75">

        <div @click.away="imgModal = false" class="max-w-3xl h-auto p-4 relative">
            <i class="ph ph-x-circle absolute top-0 right-0 -mt-4 -mr-4 bg-black bg-opacity-50 rounded-full text-white cursor-pointer hover:bg-opacity-75 p-2 text-xl z-50"
               @click="imgModal = false"></i>
            <img class="max-h-[80vh] max-w-full rounded-lg" :src="imgModalSrc" :alt="imgModalDesc">
            <p class="text-center text-white mt-2" x-text="imgModalDesc"></p>
        </div>
    </div>
</div>
</div>

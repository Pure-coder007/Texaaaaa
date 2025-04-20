<x-filament-panels::page>
    <!-- Estate Header Card -->
    <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 mb-4">
        <div class="p-4 flex items-center space-x-4">
            @if($record->getFirstMediaUrl('featured_image'))
                <img src="{{ $record->getFirstMediaUrl('featured_image') }}" alt="{{ $record->name }}" class="w-16 h-16 rounded-lg object-cover">
            @else
                <div class="w-16 h-16 bg-primary-100 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-building-office-2 class="w-8 h-8 text-primary-600" />
                </div>
            @endif
            <div>
                <h1 class="text-lg font-bold">{{ $record->name }}</h1>
                <p class="text-sm text-gray-500">{{ $record->location?->name }}, {{ optional($record->location?->city)->name }}</p>
            </div>
        </div>
    </div>

    <!-- Main Plot Selection Card -->
    <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 mb-4" x-data="{ expanded: true }">
        <div class="p-4">
            <h2 class="text-lg font-semibold mb-3">Selects Your Plots</h2>

            <!-- Plot Selection Cards -->
            <div class="space-y-4">
                @foreach($selectedPlots as $index => $plot)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3" wire:key="plot-{{ $index }}">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-medium">Plot {{ $index + 1 }}</h3>
                        @if(count($selectedPlots) > 1)
                        <button type="button" wire:click="removePlot({{ $index }})" class="text-red-500 hover:text-red-700 focus:outline-none">
                            <x-heroicon-o-trash class="w-5 h-5" />
                        </button>
                        @endif
                    </div>

                    <!-- Plot Type Selection -->
                    <div class="mb-3">
                        <label for="plot-type-{{ $index }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Plot Type
                        </label>
                        <select
                            id="plot-type-{{ $index }}"
                            wire:model="selectedPlots.{{ $index }}.plot_type_id"
                            wire:change="updatePlotType({{ $index }}, $event.target.value)"
                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 rounded-md shadow-sm text-sm focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select a plot type</option>
                            @foreach($record->plotTypes()->where('is_active', true)->get() as $plotType)
                                <option value="{{ $plotType->id }}">
                                    {{ $plotType->name }} ({{ $plotType->size_sqm }} sqm) - ₦{{ number_format($plotType->outright_price) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($plot['plot_type_id'])
                        <!-- Plot details shown after type is selected -->
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <!-- Commercial Toggle -->
                            <div class="flex items-center space-x-2">
                                <button
                                    type="button"
                                    wire:click="toggleCommercial({{ $index }})"
                                    class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 {{ $plot['is_commercial'] ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}"
                                    role="switch"
                                >
                                    <span
                                        class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200 {{ $plot['is_commercial'] ? 'translate-x-5' : 'translate-x-0' }}"
                                    ></span>
                                </button>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Commercial</span>
                            </div>

                            <!-- Corner Toggle -->
                            <div class="flex items-center space-x-2">
                                <button
                                    type="button"
                                    wire:click="toggleCorner({{ $index }})"
                                    class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 {{ $plot['is_corner'] ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}"
                                    role="switch"
                                >
                                    <span
                                        class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200 {{ $plot['is_corner'] ? 'translate-x-5' : 'translate-x-0' }}"
                                    ></span>
                                </button>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Corner</span>
                            </div>
                        </div>

                        <!-- Plot Price -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded p-2 flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $plot['name'] }} - {{ $plot['size'] }} sqm
                                @if($plot['is_commercial'] || $plot['is_corner'])
                                <span class="text-xs">
                                    ({{ $plot['is_commercial'] ? 'Commercial' : '' }}{{ $plot['is_commercial'] && $plot['is_corner'] ? ', ' : '' }}{{ $plot['is_corner'] ? 'Corner' : '' }})
                                </span>
                                @endif
                            </span>
                            <span class="font-medium text-primary-600 dark:text-primary-400">₦{{ number_format($plot['final_price']) }}</span>
                        </div>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Add Another Plot Button -->
            <button
                type="button"
                wire:click="addPlot"
                class="mt-4 w-full flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
                <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                Add Another Plot
            </button>
        </div>
    </div>

    <!-- Payment Options Card -->
    <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 mb-4">
        <div class="p-4">
            <h2 class="text-lg font-semibold mb-3">Payment Options</h2>

            <!-- Payment Plan Selection -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Plan</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <button
                        type="button"
                        wire:click="updatePaymentPlan('outright')"
                        class="relative px-3 py-2 border rounded-md text-sm font-medium transition-colors {{ $paymentPlan === 'outright' ? 'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-900 dark:border-primary-500 dark:text-primary-300' : 'border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-300' }}"
                    >
                        <span class="flex items-center justify-center">
                            @if($paymentPlan === 'outright')
                                <x-heroicon-s-check-circle class="w-4 h-4 mr-1 text-primary-600 dark:text-primary-400" />
                            @endif
                            Outright Payment
                        </span>
                    </button>

                    <button
                        type="button"
                        wire:click="updatePaymentPlan('six_month')"
                        class="relative px-3 py-2 border rounded-md text-sm font-medium transition-colors {{ $paymentPlan === 'six_month' ? 'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-900 dark:border-primary-500 dark:text-primary-300' : 'border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-300' }}"
                    >
                        <span class="flex items-center justify-center">
                            @if($paymentPlan === 'six_month')
                                <x-heroicon-s-check-circle class="w-4 h-4 mr-1 text-primary-600 dark:text-primary-400" />
                            @endif
                            6 Month Installment
                        </span>
                    </button>

                    <button
                        type="button"
                        wire:click="updatePaymentPlan('twelve_month')"
                        class="relative px-3 py-2 border rounded-md text-sm font-medium transition-colors {{ $paymentPlan === 'twelve_month' ? 'bg-primary-50 border-primary-500 text-primary-700 dark:bg-primary-900 dark:border-primary-500 dark:text-primary-300' : 'border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-300' }}"
                    >
                        <span class="flex items-center justify-center">
                            @if($paymentPlan === 'twelve_month')
                                <x-heroicon-s-check-circle class="w-4 h-4 mr-1 text-primary-600 dark:text-primary-400" />
                            @endif
                            12 Month Installment
                        </span>
                    </button>
                </div>
            </div>

            <!-- PBO Referral Code -->
            <div class="mb-4">
                <label for="pbo-code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    PBO Referral Code <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="pbo-code"
                    wire:model="pboCode"
                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 rounded-md shadow-sm text-sm focus:ring-primary-500 focus:border-primary-500"
                    placeholder="Enter PBO code"
                    required
                >
                @error('pbo_code')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Promo Code (Optional) -->
            <div class="mb-4">
                <label for="promo-code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Promo Code (Optional)
                </label>
                <div class="flex space-x-2">
                    <input
                        type="text"
                        id="promo-code"
                        wire:model="promoCode"
                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 rounded-md shadow-sm text-sm focus:ring-primary-500 focus:border-primary-500"
                        placeholder="Enter promo code if available"
                    >
                    <button
                        type="button"
                        wire:click="updatePromoCode"
                        class="flex-shrink-0 px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        Apply
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Summary Card -->
    <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 mb-4">
        <div class="p-4">
            <h2 class="text-lg font-semibold mb-3">Order Summary</h2>

            <div class="space-y-2">
                <div class="flex justify-between pb-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                    <span class="font-medium">₦{{ number_format($subtotal) }}</span>
                </div>

                @if($discount > 0)
                <div class="flex justify-between pb-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400">Promo Discount</span>
                    <span class="font-medium text-green-600 dark:text-green-400">-₦{{ number_format($discount) }}</span>
                </div>
                @endif

                <div class="flex justify-between pb-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-gray-600 dark:text-gray-400">Total Plots</span>
                    <span class="font-medium">
                        {{ count($selectedPlots) }}
                        @if($freePlotCount > 0)
                            + {{ $freePlotCount }} free
                        @endif
                    </span>
                </div>

                <div class="flex justify-between pt-2">
                    <span class="text-lg font-bold">Total</span>
                    <span class="text-lg font-bold text-primary-600 dark:text-primary-400">₦{{ number_format($grandTotal) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms and Continue Section -->
    <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 mb-4">
        <div class="p-4">
            <div class="flex items-start mb-4">
                <div class="flex items-center h-5">
                    <input
                        id="agree-terms"
                        wire:model="agreeTerms"
                        type="checkbox"
                        class="w-4 h-4 border-gray-300 rounded text-primary-600 focus:ring-primary-500"
                    >
                </div>
                <div class="ml-3 text-sm">
                    <label for="agree-terms" class="font-medium text-gray-700 dark:text-gray-300">I agree to the <a href="#" class="text-primary-600 hover:text-primary-500">terms and conditions</a></label>
                    <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">By checking this box, you agree to our terms of service and privacy policy.</p>
                    @error('agree_terms')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a
                    href="{{ route('filament.client.resources.estates.view', ['record' => $record]) }}"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                    Cancel
                </a>
                <button
                    type="button"
                    wire:click="proceedToPurchase"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        <span class="flex items-center">
                            <x-heroicon-s-shopping-cart class="w-5 h-5 mr-2" />
                            Proceed to Purchase
                        </span>
                    </button>
                </div>
            </div>
        </div>

        @if($record->promos()->where('is_active', true)->exists())
        <!-- Current Promotions Card (Collapsible) -->
        <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 mb-20" x-data="{ open: false }">
            <div class="p-4">
                <button
                    type="button"
                    class="flex w-full justify-between items-center"
                    @click="open = !open"
                >
                    <h2 class="text-lg font-semibold">Current Promotions</h2>
                    <x-heroicon-o-chevron-down class="w-5 h-5 transform transition-transform" :class="{'rotate-180': open}" />
                </button>

                <div x-show="open" x-collapse>
                    <div class="mt-3 space-y-3">
                        @foreach($record->promos()->where('is_active', true)->get() as $promo)
                        <div class="border border-primary-100 dark:border-primary-900 bg-primary-50 dark:bg-primary-900/30 rounded-lg p-3">
                            <h3 class="font-medium text-primary-700 dark:text-primary-300">{{ $promo->name }}</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $promo->description }}</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-200">
                                    Buy {{ $promo->buy_quantity }} Get {{ $promo->free_quantity }} Free
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                                    Valid until {{ $promo->valid_to->format('M d, Y') }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Sticky bottom bar for mobile -->
        <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 shadow-lg border-t border-gray-200 dark:border-gray-700 p-3 z-50">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                    <p class="text-lg font-bold text-primary-600 dark:text-primary-400">₦{{ number_format($grandTotal) }}</p>
                </div>
                <button
                    type="button"
                    wire:click="proceedToPurchase"
                    class="px-4 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                    <span class="flex items-center">
                        <x-heroicon-s-shopping-cart class="w-5 h-5 mr-2" />
                        Proceed to Purchase
                    </span>
                </button>
            </div>
        </div>

        <!-- Error message for plots -->
        @error('plots')
        <div class="fixed bottom-20 left-0 right-0 mx-auto max-w-md px-4 z-50">
            <div class="bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800 rounded-lg p-3 flex items-center text-red-700 dark:text-red-300">
                <x-heroicon-s-exclamation-circle class="w-5 h-5 mr-2 flex-shrink-0" />
                <p>{{ $message }}</p>
            </div>
        </div>
        @enderror

</x-filament-panels::page>
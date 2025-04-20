<div>
    <!-- Breadcrumb -->
    <div class="bg-gray-100">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center text-sm text-gray-600">
                <a href="{{ route('home') }}" class="hover:text-primary">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('home') }}" class="hover:text-primary">Estates</a>
                <span class="mx-2">/</span>
                <a href="{{ route('estates.show', $estate->id) }}" class="hover:text-primary">{{ $estate->name }}</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">Purchase</span>
            </div>
        </div>
    </div>

    <!-- Page Header -->
    <div class="bg-white border-b">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Complete Your Purchase</h1>
                    <p class="mt-1 text-gray-600">
                        {{ count($selectedPlots) }} plot{{ count($selectedPlots) > 1 ? 's' : '' }} selected from {{ $estate->name }}
                    </p>
                </div>

                <div class="bg-primary/10 text-primary px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap">
                    Ref: {{ $transactionReference }}
                </div>
            </div>
        </div>
    </div>

    <!-- Steps Progress Bar -->
    <div class="container mx-auto px-4 pt-8 pb-4">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 flex items-center justify-center rounded-full {{ $currentStep >= 1 ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600' }} font-medium">
                        1
                    </div>
                    <div class="ml-2">
                        <span class="text-sm font-medium {{ $currentStep >= 1 ? 'text-primary' : 'text-gray-500' }}">Review Details</span>
                    </div>
                </div>

                <div class="w-20 h-1 bg-gray-200 mx-2 sm:mx-4">
                    <div class="h-full bg-primary" style="width: {{ $currentStep > 1 ? '100%' : '0%' }}"></div>
                </div>

                <div class="flex items-center">
                    <div class="w-8 h-8 flex items-center justify-center rounded-full {{ $currentStep >= 2 ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600' }} font-medium">
                        2
                    </div>
                    <div class="ml-2">
                        <span class="text-sm font-medium {{ $currentStep >= 2 ? 'text-primary' : 'text-gray-500' }}">Payment Method</span>
                    </div>
                </div>

                <div class="w-20 h-1 bg-gray-200 mx-2 sm:mx-4">
                    <div class="h-full bg-primary" style="width: {{ $currentStep > 2 ? '100%' : '0%' }}"></div>
                </div>

                <div class="flex items-center">
                    <div class="w-8 h-8 flex items-center justify-center rounded-full {{ $currentStep >= 3 ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600' }} font-medium">
                        3
                    </div>
                    <div class="ml-2">
                        <span class="text-sm font-medium {{ $currentStep >= 3 ? 'text-primary' : 'text-gray-500' }}">Upload Proof</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 pb-12">
        <div class="max-w-4xl mx-auto">
            <!-- Session Messages -->
            @if(session()->has('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 flex items-start">
                    <i class="ph ph-warning-circle text-2xl mr-3"></i>
                    <div>
                        <p class="font-medium">Error</p>
                        <p class="mt-1">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- Step 1: Details Review -->
            <div class="{{ $currentStep == 1 ? 'block' : 'hidden' }}">
                <!-- Purchase Summary Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-primary text-white">
                        <h2 class="text-xl font-bold">Purchase Summary</h2>
                    </div>

                    <div class="p-6">
                        <!-- Estate Details -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Estate Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Estate Name</p>
                                    <p class="font-medium">{{ $estate->name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Location</p>
                                    <p class="font-medium">{{ $estate->location->name }}, {{ $estate->city->name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Total Plots Selected</p>
                                    <p class="font-medium">{{ count($selectedPlots) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Total Area</p>
                                    <p class="font-medium">{{ number_format($totalArea) }} sqm</p>
                                </div>
                            </div>
                        </div>

                       <!-- Selected Plots -->
                        {{-- <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Selected Plots</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Quantity
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Type
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Size
                                            </th>

                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Attributes
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Unit Price
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Total Price
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @php
                                            // Group plots by type, area, and attributes
                                            $groupedPlots = collect($plotDetails)->groupBy(function($plot) {
                                                return $plot['type'] . '_' . $plot['area'] . '_' . ($plot['is_commercial'] ? '1' : '0') . '_' . ($plot['is_corner'] ? '1' : '0');
                                            });
                                        @endphp

                                        @foreach($groupedPlots as $groupKey => $group)
                                            @php
                                                $firstPlot = $group->first();
                                                $count = $group->count();
                                                $plotNumbers = $group->pluck('number')->toArray();
                                                $totalPrice = $group->sum('price');
                                            @endphp
                                            <tr>
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                    {{ $count }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-600">
                                                    {{ $firstPlot['type'] }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-600">
                                                    {{ number_format($firstPlot['area']) }} sqm
                                                </td>

                                                <td class="px-4 py-3 text-sm text-gray-600">
                                                    @if($firstPlot['is_commercial'] || $firstPlot['is_corner'])
                                                        <div class="flex space-x-1">
                                                            @if($firstPlot['is_commercial'])
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                    Commercial (+{{ $estate->commercial_plot_premium_percentage }}%)
                                                                </span>
                                                            @endif
                                                            @if($firstPlot['is_corner'])
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                                    Corner (+{{ $estate->corner_plot_premium_percentage }}%)
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-900 text-right whitespace-nowrap">
                                                    ₦{{ number_format($firstPlot['price']) }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-900 text-right whitespace-nowrap font-medium">
                                                    ₦{{ number_format($totalPrice) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div> --}}

                        <!-- Price Breakdown -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Price Breakdown</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Base Price:</span>
                                        <span class="font-medium">₦{{ number_format($basePrice) }}</span>
                                    </div>

                                    @if($premiumAmount > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Plot Additional Fee (Commercial/Corner):</span>
                                        <span class="font-medium">₦{{ number_format($premiumAmount) }}</span>
                                    </div>
                                    @endif

                                    @if($discountAmount > 0)
                                    <div class="flex justify-between text-green-600">
                                        <span>Discount:</span>
                                        <span class="font-medium">-₦{{ number_format($discountAmount) }}</span>
                                    </div>
                                    @endif

                                    <div class="pt-2 mt-1 border-t border-gray-200 flex justify-between">
                                        <span class="font-medium text-gray-700">Subtotal:</span>
                                        <span class="font-medium">₦{{ number_format($totalPrice) }}</span>
                                    </div>

                                    @if($showTax && $taxAmount > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">{{ $taxName }} ({{ $taxPercentage }}%):</span>
                                        <span>₦{{ number_format($taxAmount) }}</span>
                                    </div>
                                    @endif

                                    @if($showProcessingFee && $processingFee > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">
                                            {{ $processingFeeName }}
                                            @if($processingFeeType === 'percentage')
                                            ({{ $systemSettings->processing_fee_value }}%)
                                            @endif
                                        </span>
                                        <span>₦{{ number_format($processingFee) }}</span>
                                    </div>
                                    @endif

                                    <div class="pt-2 mt-1 border-t border-gray-200 flex justify-between font-bold">
                                        <span>Total:</span>
                                        <span class="text-primary">₦{{ number_format($totalWithFeesAndTax) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Plan Details -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Payment Plan</h3>
                            <div class="bg-white rounded-lg border border-gray-200 p-4">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-4">
                                    <div class="bg-primary/10 text-primary px-4 py-2 rounded-lg text-sm font-medium">
                                        {{ $paymentPlanType === 'outright' ? 'Outright Payment' : ($paymentPlanType === '6_months' ? '6-Month Installment' : '12-Month Installment') }}
                                    </div>

                                    @if($paymentPlanType !== 'outright')
                                    <div class="flex items-center text-gray-600 text-sm">
                                        <i class="ph ph-calendar mr-1"></i>
                                        <span>{{ $paymentPlanType === '6_months' ? '6' : '12' }} months payment period</span>
                                    </div>
                                    @endif
                                </div>

                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">{{ $paymentPlanType === 'outright' ? 'Total Amount Due:' : 'Initial Payment:' }}</span>
                                        <span class="font-medium">₦{{ number_format($initialPayment) }}</span>
                                    </div>

                                    @if($paymentPlanType !== 'outright')
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Remaining Balance:</span>
                                        <span class="font-medium">₦{{ number_format($remainingAmount) }}</span>
                                    </div>

                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Due Date:</span>
                                        <span class="font-medium">{{ now()->addMonths($paymentPlanType === '6_months' ? 6 : 12)->format('M d, Y') }}</span>
                                    </div>
                                    @endif
                                </div>

                                @if($paymentPlanType !== 'outright')
                                <!-- Installment Payment Info -->
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <div class="text-sm text-gray-600">
                                        <p class="flex items-start mb-2">
                                            <i class="ph ph-info text-blue-500 mr-2 mt-0.5"></i>
                                            <span>With our flexible installment plan, you can pay any amount at any time within the payment period.</span>
                                        </p>
                                        <p class="flex items-start">
                                            <i class="ph ph-check-circle text-green-500 mr-2 mt-0.5"></i>
                                            <span>No fixed monthly payments required - just complete the full payment by the due date.</span>
                                        </p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Free Plots Information (if applicable) -->
                        @if($freePlots > 0 && $paymentPlanType === 'outright')
                        <div class="mb-6">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="ph ph-gift text-2xl text-green-600"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-green-800">Promotional Offer Applied!</h3>
                                        <div class="mt-2 text-sm text-green-700">
                                            <p>Congratulations! You qualify for {{ $freePlots }} free plot(s) with your purchase.</p>
                                            <p class="mt-1 text-xs">The smallest available plots have been automatically selected as your free plots.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Agent Information (if applicable) -->
                        @if($agent)
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">PBO Information</h3>
                            <div class="bg-white rounded-lg border border-gray-200 p-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @if($agent->getMedia('avatar_url')->first())
                                            <img class="h-12 w-12 rounded-full" src="{{ $agent->getMedia('avatar_url')->first()->getUrl() }}" alt="{{ $agent->name }}">
                                        @else
                                            <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                                                {{ strtoupper(substr($agent->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-base font-medium text-gray-900">{{ $agent->name }}</h4>
                                        <p class="text-sm text-gray-600">PBO Code: {{ $pboCode }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex justify-end mt-8">
                            <button type="button" wire:click="nextStep" class="px-6 py-3 bg-primary hover:bg-primary-dark text-white font-medium rounded-lg shadow-sm transition-colors flex items-center">
                                Continue to Payment Method
                                <i class="ph ph-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Payment Method Selection -->
            <div class="{{ $currentStep == 2 ? 'block' : 'hidden' }}">
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-primary text-white">
                        <h2 class="text-xl font-bold">Select Payment Method</h2>
                    </div>

                    <div class="p-6">
                        <div class="space-y-6">
                            <!-- Bank Transfer Option -->
                            @if($this->isBankTransferEnabled)
                            <div class="border rounded-lg p-4 relative {{ $paymentMethod === 'bank_transfer' ? 'border-primary bg-primary/5' : 'border-gray-200' }}">
                                <input type="radio" id="bank_transfer" wire:click="changePaymentMethod('bank_transfer')" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" {{ $paymentMethod === 'bank_transfer' ? 'checked' : '' }}>

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center">
                                            <i class="ph ph-bank text-blue-600 text-xl"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-base font-medium text-gray-900">Bank Transfer</h3>
                                            <p class="text-sm text-gray-500">Make a direct transfer to our bank account</p>
                                        </div>
                                    </div>
                                    <div class="h-5 w-5 bg-white border {{ $paymentMethod === 'bank_transfer' ? 'border-primary' : 'border-gray-300' }} rounded-full flex items-center justify-center">
                                        <div class="{{ $paymentMethod === 'bank_transfer' ? 'h-3 w-3 bg-primary rounded-full' : '' }}"></div>
                                    </div>
                                </div>

                                <!-- Bank Transfer Details (shows when selected) -->
                                @if($paymentMethod === 'bank_transfer')
                                <div class="mt-4 pt-4 border-t border-gray-200 relative z-20">
                                    <div class="mb-4">
                                        <label for="bank_account" class="block text-sm font-medium text-gray-700 mb-1">
                                            Select Bank Account
                                        </label>
                                        <select id="bank_account" wire:model.live="selectedBankId" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                                            <option value="">-- Select a bank account --</option>
                                            @foreach($bankAccounts as $account)
                                                <option value="{{ $account['id'] }}">{{ $account['bank_name'] }} - {{ $account['account_number'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @if($selectedBankDetails)
                                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                        <h4 class="font-medium text-gray-800 mb-2">Transfer Details</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                            <div>
                                                <p class="text-gray-500">Bank Name</p>
                                                <p class="font-medium">{{ $selectedBankDetails['bank_name'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Account Number</p>
                                                <p class="font-medium">{{ $selectedBankDetails['account_number'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Account Name</p>
                                                <p class="font-medium">{{ $selectedBankDetails['account_name'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Reference</p>
                                                <p class="font-medium text-primary">{{ $transactionReference }}</p>
                                            </div>
                                        </div>
                                        <div class="mt-3 text-sm text-gray-600">
                                            <p><strong>Important:</strong> Please use the reference number above when making your transfer.</p>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="text-sm text-gray-600">
                                        <p class="flex items-start mb-2">
                                            <i class="ph ph-info text-blue-500 mr-2 mt-0.5"></i>
                                            <span>After making the transfer, you'll need to upload proof of payment on the next step.</span>
                                        </p>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif

                            <!-- Cash Payment Option -->
                            @if($this->isCashPaymentEnabled)
                            <div class="border rounded-lg p-4 relative {{ $paymentMethod === 'cash' ? 'border-primary bg-primary/5' : 'border-gray-200' }}">
                                <input type="radio" id="cash" wire:click="changePaymentMethod('cash')" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" {{ $paymentMethod === 'cash' ? 'checked' : '' }}>

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-green-50 rounded-full flex items-center justify-center">
                                            <i class="ph ph-money text-green-600 text-xl"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-base font-medium text-gray-900">Cash Payment</h3>
                                            <p class="text-sm text-gray-500">Pay at our office and upload receipt</p>
                                        </div>
                                    </div>
                                    <div class="h-5 w-5 bg-white border {{ $paymentMethod === 'cash' ? 'border-primary' : 'border-gray-300' }} rounded-full flex items-center justify-center">
                                        <div class="{{ $paymentMethod === 'cash' ? 'h-3 w-3 bg-primary rounded-full' : '' }}"></div>
                                    </div>
                                </div>

                                <!-- Cash Payment Details (shows when selected) -->
                                @if($paymentMethod === 'cash')
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                        <h4 class="font-medium text-gray-800 mb-2">Office Details</h4>
                                        <div class="space-y-2 text-sm">
                                            <div>
                                                <p class="text-gray-500">Address</p>
                                                <p class="font-medium">{{ $systemSettings->cash_payment_office_address }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Office Hours</p>
                                                <p class="font-medium">{{ $systemSettings->cash_payment_office_hours }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Reference Number</p>
                                                <p class="font-medium text-primary">{{ $transactionReference }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-sm text-gray-600">
                                        <p class="flex items-start">
                                            <i class="ph ph-info text-green-500 mr-2 mt-0.5"></i>
                                            <span>{{ $systemSettings->cash_payment_instructions }}</span>
                                        </p>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Amount to Pay Summary -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row justify-between md:items-center">
                            <div class="mb-4 md:mb-0">
                                <p class="text-gray-600">Amount Due:</p>
                                <p class="text-2xl font-bold text-primary">₦{{ number_format($initialPayment) }}</p>
                                <p class="text-sm text-gray-500">Reference: {{ $transactionReference }}</p>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Please make your payment for the amount shown and proceed to upload your payment proof in the next step.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-between mt-8">
                    <button type="button" wire:click="previousStep" class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg shadow-sm hover:bg-gray-50 transition-colors flex items-center">
                        <i class="ph ph-arrow-left mr-2"></i>
                        Back
                    </button>

                    <button type="button" wire:click="nextStep" class="px-6 py-3 bg-primary hover:bg-primary-dark text-white font-medium rounded-lg shadow-sm transition-colors flex items-center">
                        Continue to Upload Proof
                        <i class="ph ph-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- Step 3: Upload Payment Proof -->
            <div class="{{ $currentStep == 3 ? 'block' : 'hidden' }}">
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-primary text-white">
                        <h2 class="text-xl font-bold">Upload Payment Proof</h2>
                    </div>

                    <div class="p-6">
                        <form wire:submit.prevent="submitPaymentProof">
                            <div class="space-y-6">
                                <!-- Payment Method Summary -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $paymentMethod === 'bank_transfer' ? 'bg-blue-50 text-blue-600' : 'bg-green-50 text-green-600' }}">
                                            <i class="ph {{ $paymentMethod === 'bank_transfer' ? 'ph-bank' : 'ph-money' }} text-xl"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-base font-medium text-gray-900">
                                                {{ $paymentMethod === 'bank_transfer' ? 'Bank Transfer' : 'Cash Payment' }}
                                            </h3>
                                            <p class="text-sm text-gray-600">
                                                {{ $paymentMethod === 'bank_transfer' ? 'Transfer to bank account' : 'Paid at office' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Amount Paid:</span>
                                            <span class="font-medium">₦{{ number_format($initialPayment) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Reference:</span>
                                            <span class="font-medium">{{ $transactionReference }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Proof Upload -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Upload Payment Proof
                                    </label>
                                    <div
                                        x-data="{ isUploading: false, progress: 0 }"
                                        x-on:livewire-upload-start="isUploading = true"
                                        x-on:livewire-upload-finish="isUploading = false"
                                        x-on:livewire-upload-error="isUploading = false"
                                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                                        class="flex justify-center rounded-lg border border-dashed border-gray-300 py-8 px-4"
                                    >
                                        <div class="text-center">
                                            <div class="mb-3">
                                                @if($paymentProofFile)
                                                    <div class="mb-3">
                                                        @if(in_array(strtolower($paymentProofFile->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'gif']))
                                                            <img src="{{ $paymentProofFile->temporaryUrl() }}" alt="Payment Proof" class="mx-auto h-40 object-contain rounded-lg shadow-sm">
                                                        @else
                                                            <div class="mx-auto h-40 flex items-center justify-center bg-gray-100 rounded-lg">
                                                                <div class="text-center">
                                                                    <i class="ph ph-file-text text-4xl text-primary mb-2"></i>
                                                                    <p class="text-sm text-gray-700">{{ $paymentProofFile->getClientOriginalName() }}</p>
                                                                    <p class="text-xs text-gray-500">{{ round($paymentProofFile->getSize() / 1024) }} KB</p>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <i class="ph ph-upload-simple text-4xl text-gray-400"></i>
                                                @endif
                                            </div>

                                            <div class="flex text-sm text-gray-600">
                                                <label for="payment-proof" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-primary-dark" />
                                                    <span>{{ $paymentProofFile ? 'Change file' : 'Upload a file' }}</span>
                                                    <input id="payment-proof" wire:model="paymentProofFile" type="file" class="sr-only" />
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>

                                            <p class="text-xs text-gray-500 mt-1">
                                                PNG, JPG, GIF up to 5MB
                                            </p>

                                            <!-- Progress Bar -->
                                            <div x-show="isUploading" class="mt-3 w-full bg-gray-200 rounded-full h-2.5">
                                                <div class="bg-primary h-2.5 rounded-full" x-bind:style="'width: ' + progress + '%'"></div>
                                            </div>

                                            @error('paymentProofFile')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Notes -->
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                        Additional Notes (Optional)
                                    </label>
                                    <textarea id="notes" wire:model="notes" rows="3" class="w-full border rounded-lg border-gray-300 focus:border-primary focus:ring-primary shadow-sm"></textarea>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Any additional information about your payment.
                                    </p>
                                </div>

                                <!-- Important Notes -->
                                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="ph ph-warning text-yellow-700 text-lg"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-yellow-800">Important Notes</h3>
                                            <div class="mt-2 text-sm text-yellow-700">
                                                <ul class="list-disc list-inside space-y-1">
                                                    <li>Payments will be verified by our team within 24-48 hours.</li>
                                                    <li>Make sure your payment proof clearly shows the amount, date, and reference.</li>
                                                    <li>{{ $paymentMethod === 'bank_transfer' ? 'Ensure you\'ve included the reference in your transfer description.' : 'Keep your receipt safe until your payment is confirmed.' }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="flex justify-between mt-8">
                                    <button type="button"
                                            wire:click="previousStep"
                                            class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg shadow-sm hover:bg-gray-50 transition-colors flex items-center"
                                            @if($isProcessing) disabled @endif>
                                        <i class="ph ph-arrow-left mr-2"></i>
                                        Back
                                    </button>

                                    <button type="submit"
                                            class="px-6 py-3 bg-primary hover:bg-primary-dark text-white font-medium rounded-lg shadow-sm transition-colors flex items-center"
                                            wire:loading.class="opacity-50 cursor-not-allowed"
                                            wire:loading.attr="disabled"
                                            wire:target="submitPaymentProof">
                                        <div wire:loading wire:target="submitPaymentProof" class="mr-2">
                                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                        <i wire:loading.remove wire:target="submitPaymentProof" class="ph ph-check-circle mr-2"></i>
                                        <span wire:loading.remove wire:target="submitPaymentProof">Complete Purchase</span>
                                        <span wire:loading wire:target="submitPaymentProof">Processing...</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

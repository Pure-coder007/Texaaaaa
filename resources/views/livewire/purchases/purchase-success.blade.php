<div>
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center justify-center mb-6">
                <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Purchase Completed Successfully!</h1>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Transaction Details</h2>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Transaction Reference:</span>
                                <span class="font-medium">{{ $transactionReference ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Purchase Date:</span>
                                <span class="font-medium">{{ $purchaseDate ?? now()->format('F d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Payment Method:</span>
                                <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $paymentMethod ?? 'bank transfer')) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Payment Plan:</span>
                                <span class="font-medium">
                                    @if(isset($paymentPlanType) && $paymentPlanType === 'outright')
                                        Outright Payment
                                    @elseif(isset($paymentPlanType) && $paymentPlanType === '6_months')
                                        6 Months Installment
                                    @elseif(isset($paymentPlanType) && $paymentPlanType === '12_months')
                                        12 Months Installment
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Amount Paid:</span>
                                <span class="font-medium">₦{{ isset($purchaseAmount) ? number_format($purchaseAmount, 2) : '0.00' }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Next Steps</h2>
                        <div class="space-y-3">
                            @if(isset($paymentPlanType) && $paymentPlanType === 'outright' && isset($isFullyPaid) && $isFullyPaid)
                                <p class="text-gray-600">
                                    Your payment has been received and your purchase is now complete. You can download your receipt, sales agreement, and allocation letter below.
                                </p>
                            @elseif(isset($paymentPlanType) && $paymentPlanType === 'outright' && isset($isFullyPaid) && !$isFullyPaid)
                                <p class="text-gray-600">
                                    Your initial payment has been received. Once your payment is verified, you'll receive your allocation letter. You can download your receipt and sales agreement below.
                                </p>
                            @else
                                <p class="text-gray-600">
                                    Your initial payment has been received. Please continue with your installment payments according to your selected plan. You can download your receipt below. The sales agreement will be provided once all installment payments are completed.
                                </p>
                            @endif
                            <p class="text-gray-600">
                                You will also receive an email with these documents attached. You can view all your documents in your dashboard at any time.
                            </p>
                            <div class="mt-4">
                                <a href="{{ route('filament.client.pages.dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                                    Go to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Your Documents</h2>

            @if(isset($documents) && count($documents) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($documents as $document)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center mb-3">
                                <div class="p-2 bg-blue-100 rounded-lg mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="font-semibold text-gray-700">{{ $document->name }}</h3>
                            </div>

                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">
                                    {{ ucfirst(str_replace('_', ' ', $document->document_type)) }}
                                </span>

                                @if($document->getFirstMedia('document_file'))
                                    <a href="{{ $document->getFirstMediaUrl('document_file') }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium">
                                        Download
                                    </a>
                                @else
                                    <span class="text-gray-400">Processing...</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Your documents are being processed. Please check back later or view them in your dashboard.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- <div class="mt-8 text-center">
                <p class="text-gray-600 mb-4">Need assistance or have questions about your purchase?</p>
                <a href="{{ route('contact') }}" class="text-blue-600 hover:text-blue-800 font-medium">Contact our support team</a>
            </div> --}}
        </div>
    </div>
</div>
<x-filament::page>
    <x-filament::section>
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900">Transaction #{{ $record->transaction_id }}</h2>
            <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <span class="block text-sm font-medium text-gray-500">Client</span>
                    <span class="block mt-1">{{ $record->client->name }}</span>
                </div>
                <div>
                    <span class="block text-sm font-medium text-gray-500">Estate</span>
                    <span class="block mt-1">{{ $record->estate->name }}</span>
                </div>
                <div>
                    <span class="block text-sm font-medium text-gray-500">Total Amount</span>
                    <span class="block mt-1">NGN {{ number_format($record->total_amount, 2) }}</span>
                </div>
                <div>
                    <span class="block text-sm font-medium text-gray-500">Payment Type</span>
                    <span class="block mt-1">
                        @php
                            $paymentType = match($record->payment_plan_type) {
                                'outright' => 'Outright',
                                '6_months' => '6 Months Plan',
                                '12_months' => '12 Months Plan',
                                default => $record->payment_plan_type,
                            };
                        @endphp
                        {{ $paymentType }}
                    </span>
                </div>
                <div>
                    <span class="block text-sm font-medium text-gray-500">Paid Amount</span>
                    <span class="block mt-1">NGN {{ number_format($record->totalPaid(), 2) }}</span>
                </div>
                <div>
                    <span class="block text-sm font-medium text-gray-500">Remaining Balance</span>
                    <span class="block mt-1">NGN {{ number_format($record->remainingBalance(), 2) }}</span>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-8">
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            You have {{ $pendingPayments }} pending payment{{ $pendingPayments !== 1 ? 's' : '' }} to verify for this transaction.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if($payment)
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border">
                <h3 class="text-lg font-medium text-gray-900">Selected Payment Details</h3>

                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <span class="block text-sm font-medium text-gray-500">Amount</span>
                        <span class="block mt-1">NGN {{ number_format($payment->amount, 2) }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-500">Date</span>
                        <span class="block mt-1">{{ $payment->created_at->format('M d, Y g:i A') }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-500">Method</span>
                        <span class="block mt-1">{{ $payment->payment_method }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-500">Type</span>
                        <span class="block mt-1">{{ $payment->payment_type }}</span>
                    </div>
                </div>

                @if($payment->paymentProof)
                    <div class="mt-4">
                        <h4 class="text-sm font-medium text-gray-500">Payment Proof</h4>
                        <div class="mt-2">
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                @if($payment->paymentProof->hasMedia('proof_documents'))
                                    @php
                                        $media = $payment->paymentProof->getFirstMedia('proof_documents');
                                        $isImage = in_array($media->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
                                    @endphp

                                    @if($isImage)
                                        <div class="text-center">
                                            <img src="{{ $media->getUrl() }}" alt="Payment Proof" class="max-w-full h-auto max-h-64">
                                            <div class="mt-2">
                                                <a href="{{ $media->getUrl() }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                                    View Full Image
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="mt-2">
                                                <a href="{{ $media->getUrl() }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                                    Download Document
                                                </a>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">{{ $media->file_name }}</p>
                                        </div>
                                    @endif
                                @else
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <p class="text-sm text-gray-500">No document attached</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if($payment->transaction_id)
                    <div class="mt-4">
                        <span class="block text-sm font-medium text-gray-500">Transaction ID</span>
                        <span class="block mt-1">{{ $payment->transaction_id }}</span>
                    </div>
                @endif

                @if($payment->payment_details)
                    <div class="mt-4">
                        <span class="block text-sm font-medium text-gray-500">Additional Details</span>
                        <div class="mt-1 text-sm text-gray-900">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                                @foreach($payment->payment_details as $key => $value)
                                    <div>
                                        <dt class="text-gray-500">{{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                                        <dd>{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{ $this->form }}
    </x-filament::section>
</x-filament::page>
<div>
@php
    $payment = \App\Models\Payment::find($getState() ?? $getLivewire()->data['payment_id'] ?? null);
@endphp

@if ($payment)
    <div class="rounded-md bg-white">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
            <div>
                <dt class="text-sm font-medium text-gray-500">Amount</dt>
                <dd class="mt-1 text-sm text-gray-900">NGN {{ number_format($payment->amount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Date</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $payment->created_at->format('M d, Y g:i A') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Method</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $payment->payment_method }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Type</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $payment->payment_type }}</dd>
            </div>

            @if ($payment->transaction_id)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Transaction ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $payment->transaction_id }}</dd>
                </div>
            @endif
        </dl>

        @if ($payment->paymentProof)
            <div class="mt-4">
                <dt class="text-sm font-medium text-gray-500">Payment Proof</dt>
                <dd class="mt-1">
                    @if ($payment->paymentProof->hasMedia('proof_documents'))
                        <a
                            href="{{ $payment->paymentProof->getFirstMediaUrl('proof_documents') }}"
                            target="_blank"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                            </svg>
                            View Payment Proof
                        </a>
                    @else
                        <span class="text-sm text-gray-500">No proof document attached</span>
                    @endif
                </dd>
            </div>
        @endif
    </div>
@else
    <div class="text-sm text-gray-500">Select a payment to view details</div>
@endif
</div>
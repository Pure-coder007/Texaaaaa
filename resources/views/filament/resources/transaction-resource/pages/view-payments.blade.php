<x-filament::page>
    <x-filament::section>
        <div class="mb-4">
            <h2 class="text-xl font-bold text-gray-900">Transaction Details</h2>
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

        <div class="mt-8">
            <h2 class="text-xl font-bold text-gray-900">Payment History</h2>

            @php
                $payments = $record->payments()->with(['paymentProof'])->latest()->get();
            @endphp

            @if($payments->isEmpty())
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mt-4">
                    <p class="text-gray-500 text-center">No payments have been recorded for this transaction yet.</p>
                </div>
            @else
                <div class="mt-4 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Date</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Method</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Proof</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($payments as $payment)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                        {{ $payment->created_at->format('M d, Y g:i A') }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $payment->payment_method }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $payment->payment_type }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        NGN {{ number_format($payment->amount, 2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        @php
                                            $statusColor = match($payment->status) {
                                                'verified' => 'bg-green-100 text-green-800',
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'failed' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColor }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        @if($payment->paymentProof)
                                            <button
                                                type="button"
                                                onclick="window.open('{{ $payment->paymentProof->getFirstMediaUrl('proof_documents') }}', '_blank')"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                View Proof
                                            </button>
                                        @else
                                            <span class="text-gray-400">No proof</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament::page>
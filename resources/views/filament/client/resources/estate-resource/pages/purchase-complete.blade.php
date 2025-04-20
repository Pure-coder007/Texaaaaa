<x-filament-panels::page>
    <div class="text-center py-10">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-100 text-primary-600 mb-6">
            <x-heroicon-o-check-circle class="w-8 h-8" />
        </div>

        <h1 class="text-2xl font-bold mb-2">Thank You for Your Purchase!</h1>

        <p class="text-lg text-gray-600 dark:text-gray-300 mb-6">
            Your purchase has been successfully processed.
        </p>

        @if($purchase)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-md mx-auto mb-8 dark:bg-gray-800 dark:border-gray-700">
                <div class="mb-4">
                    <div class="text-gray-500 text-sm">Purchase Reference</div>
                    <div class="font-medium">{{ $purchase->transaction_id }}</div>
                </div>

                <div class="mb-4">
                    <div class="text-gray-500 text-sm">Estate</div>
                    <div class="font-medium">{{ $purchase->estate->name }}</div>
                </div>

                <div class="mb-4">
                    <div class="text-gray-500 text-sm">Total Amount</div>
                    <div class="font-medium">₦{{ number_format($purchase->total_amount) }}</div>
                </div>

                <div class="mb-4">
                    <div class="text-gray-500 text-sm">Payment Plan</div>
                    <div class="font-medium">
                        @switch($purchase->payment_plan_type)
                            @case('outright')
                                Outright Payment
                                @break
                            @case('six_month')
                                6-Month Installment
                                @break
                            @case('twelve_month')
                                12-Month Installment
                                @break
                            @default
                                {{ $purchase->payment_plan_type }}
                        @endswitch
                    </div>
                </div>

                <div>
                    <div class="text-gray-500 text-sm">Status</div>
                    <div class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-sm font-medium text-orange-700">
                        <span class="mr-1">Pending Verification</span>
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                        </span>
                    </div>
                </div>
            </div>
        @endif

        <div class="max-w-2xl mx-auto">
            <h2 class="text-lg font-medium mb-4">What happens next?</h2>

            <div class="space-y-6">
                <div class="flex">
                    <div class="flex-shrink-0 mr-4">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-100 text-primary-600">
                            1
                        </div>
                    </div>
                    <div>
                        <h3 class="text-base font-medium">Payment Verification</h3>
                        <p class="text-gray-500 mt-1">
                            Our team will verify your payment within 24 hours. You'll receive an email confirmation once it's verified.
                        </p>
                    </div>
                </div>

                <div class="flex">
                    <div class="flex-shrink-0 mr-4">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-100 text-primary-600">
                            2
                        </div>
                    </div>
                    <div>
                        <h3 class="text-base font-medium">Documentation</h3>
                        <p class="text-gray-500 mt-1">
                            Your receipt and purchase contract will be available in your account once payment is confirmed.
                            @if($purchase && $purchase->payment_plan_type === 'outright')
                                An allocation letter will also be generated.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex">
                    <div class="flex-shrink-0 mr-4">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-100 text-primary-600">
                            3
                        </div>
                    </div>
                    <div>
                        <h3 class="text-base font-medium">Physical Allocation</h3>
                        <p class="text-gray-500 mt-1">
                            You can schedule a visit for the physical allocation of your plot once payment is complete.
                            @if($purchase && $purchase->payment_plan_type !== 'outright')
                                For installment plans, physical allocation occurs after full payment.
                            @endif
                        </p>
                    </div>
                </div>

                @if($purchase && $purchase->payment_plan_type !== 'outright')
                    <div class="flex">
                        <div class="flex-shrink-0 mr-4">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-100 text-primary-600">
                                4
                            </div>
                        </div>
                        <div>
                            <h3 class="text-base font-medium">Installment Payments</h3>
                            <p class="text-gray-500 mt-1">
                                You'll receive reminders for your upcoming payments. You can make payments anytime through your account dashboard.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('filament.client.resources.purchases.index') }}" class="inline-flex items-center justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                <x-heroicon-m-document-text class="w-5 h-5 mr-2" />
                View My Purchases
            </a>

            <a href="{{ route('filament.client.pages.dashboard') }}" class="inline-flex items-center justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                <x-heroicon-m-home class="w-5 h-5 mr-2" />
                Return to Dashboard
            </a>
        </div>
    </div>
</x-filament-panels::page>
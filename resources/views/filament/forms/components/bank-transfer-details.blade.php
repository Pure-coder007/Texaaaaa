<div class="bg-gray-50 rounded-lg p-4 mb-4">
    <h4 class="font-medium text-gray-800 mb-2">Transfer Details</h4>

    @php
        $systemSettings = app(\App\Settings\SystemSettings::class);
      
        $selectedBank = $systemSettings->getBankAccountById($this->mountedTableActionsData[0]['bank_account_id'] ?? 0);
        $transactionReference = $this->mountedTableActionsData[0]['transaction_reference'] ?? '';
    @endphp

    @if($selectedBank)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
        <div>
            <p class="text-gray-500">Bank Name</p>
            <p class="font-medium">{{ $selectedBank['bank_name'] }}</p>
        </div>
        <div>
            <p class="text-gray-500">Account Number</p>
            <p class="font-medium">{{ $selectedBank['account_number'] }}</p>
        </div>
        <div>
            <p class="text-gray-500">Account Name</p>
            <p class="font-medium">{{ $selectedBank['account_name'] }}</p>
        </div>
        <div>
            <p class="text-gray-500">Reference</p>
            <p class="font-medium text-primary">{{ $transactionReference }}</p>
        </div>
    </div>
    <div class="mt-3 text-sm text-gray-600">
        <p><strong>Important:</strong> Please use the reference number above when making your transfer.</p>
    </div>
    @endif

    <div class="mt-3 text-sm text-gray-600">
        <p class="flex items-start mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <span>After making the transfer, please upload proof of payment below.</span>
        </p>
    </div>
</div>
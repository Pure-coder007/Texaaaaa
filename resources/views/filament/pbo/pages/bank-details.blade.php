<x-filament::page>
    <div class="mb-6">
        <div class="p-6 bg-white rounded-lg shadow-sm dark:bg-gray-800">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Bank Account Details</h2>
                    <p class="text-gray-500 text-sm dark:text-gray-400">Keep your payment information up to date to receive your commissions</p>
                </div>
            </div>
        </div>
    </div>

    <div>
        <form wire:submit="save">
            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button type="submit">
                    Save Bank Details
                </x-filament::button>
            </div>
        </form>
    </div>

    @if(auth()->user()->hasBankDetails())
        <div class="mt-8">
            <div class="p-6 bg-green-50 border border-green-200 rounded-lg text-green-700 dark:bg-green-900/20 dark:border-green-700 dark:text-green-300">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Your bank details are complete. You are ready to receive commission payments.</span>
                </div>
            </div>
        </div>
    @else
        <div class="mt-8">
            <div class="p-6 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-700 dark:bg-yellow-900/20 dark:border-yellow-700 dark:text-yellow-300">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Please complete your bank details to receive commission payments.</span>
                </div>
            </div>
        </div>
    @endif
</x-filament::page>
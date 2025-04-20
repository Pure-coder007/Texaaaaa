<x-filament-panels::page.simple>
    <x-slot name="heading">
        @php
            $panelId = \Filament\Facades\Filament::getCurrentPanel()->getId();
            $heading = match ($panelId) {
                'pbo' => 'PBO Registration',
                'client' => 'Client Registration',
                default => 'Registration',
            };
        @endphp
        {{ $heading }}
    </x-slot>

    <x-slot name="subheading">
        @php
            $description = match ($panelId) {
                'pbo' => 'Create your PBO account to manage listings and clients.',
                'client' => 'Register to access property details, track payments, and more.',
                default => 'Create an account to get started.',
            };
        @endphp
        {{ $description }}

        @if($panelId === 'pbo' && $this->referralCode)
            <div class="mt-3 p-2 bg-yellow-50 border border-yellow-200 rounded-md text-yellow-700 text-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-yellow-400 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    You've been referred by an PBO. The referral code has been applied automatically.
                </div>
            </div>
        @endif

        <div class="mt-4">
            {{ __('filament-panels::pages/auth/register.actions.login.before') }}

            {{ $this->loginAction }}
        </div>

        <div class="mt-4 text-sm text-center">
            <p class="mb-2">Access the right portal for your role:</p>
            <div class="mt-4 flex justify-center">
                <div class="inline-flex rounded-md shadow-sm" role="group">
                    <a href="{{ route('filament.pbo.auth.register', ['ref' => request()->query('ref')]) }}"
                       class="px-4 py-2 text-sm font-medium border-t border-b border-r rounded-l-lg {{ $panelId === 'pbo' ? 'bg-primary-500 text-white border-primary-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                       PBO
                    </a>
                    <a href="{{ route('filament.client.auth.register') }}"
                       class="px-4 py-2 text-sm font-medium border-t border-b border-r rounded-r-lg {{ $panelId === 'client' ? 'bg-primary-500 text-white border-primary-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                        Client
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_REGISTER_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form wire:submit="register">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    @if($panelId === 'pbo')
        <div class="mt-6 text-sm text-center text-gray-500">
            <p>PBO accounts require approval before access is granted.</p>
            <p>For assistance, please contact our support team.</p>

            @if(!$this->referralCode)
                <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded text-blue-700">
                    <p class="font-semibold">Do you have a referral code?</p>
                    <p>If another PBO referred you, make sure to enter their referral code to earn both of you bonus points!</p>
                </div>
            @endif
        </div>
    @elseif($panelId === 'client')
        <div class="mt-6 text-sm text-center text-gray-500">
            <p>After registration, you'll have immediate access to your client portal.</p>
            <p>Please keep your login credentials secure.</p>
        </div>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_REGISTER_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>

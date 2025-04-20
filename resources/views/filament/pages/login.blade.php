<x-filament-panels::page.simple>
    <x-slot name="heading">
        @php
            $panelId = \Filament\Facades\Filament::getCurrentPanel()->getId();
            $heading = match ($panelId) {
                'admin' => 'Admin Portal Login',
                'pbo' => 'PBO Portal Login',
                'client' => 'Client Portal Login',
                default => 'Login',
            };
        @endphp
        {{ $heading }}
    </x-slot>

    <x-slot name="subheading">
        @if (filament()->hasRegistration())
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}
            {{ $this->registerAction }}
        @endif

        @if ($panelId !== 'admin')
        <div class="mt-3 text-sm text-center">
            <p class="mb-2">Access the right portal for your role:</p>
            <div class="mt-4 flex justify-center">
                <div class="inline-flex rounded-md shadow-sm" role="group">
                   
                    <a href="{{ route('filament.pbo.auth.login') }}"
                       class="px-4 py-2 rounded-l-lg text-sm font-medium border-t border-b border-r {{ $panelId === 'pbo' ? 'bg-primary-500 text-white border-primary-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                       PBO
                    </a>
                    <a href="{{ route('filament.client.auth.login') }}"
                       class="px-4 py-2 text-sm font-medium border-t border-b border-r rounded-r-lg {{ $panelId === 'client' ? 'bg-primary-500 text-white border-primary-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                        Client
                    </a>
                </div>
            </div>
        </div>
        @endif
    </x-slot>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="$this->hasFullWidthFormActions()" />
    </x-filament-panels::form>

    <div class="mt-6 text-center">
        @php
            $message = match ($panelId) {
                'admin' => 'Admin access is restricted to authorized personnel only.',
                'pbo' => 'For real estate PBO to manage sales and clients.',
                'client' => 'Access your property details and payment information.',
                default => '',
            };
        @endphp
        <p class="text-sm text-gray-500">{{ $message }}</p>
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>

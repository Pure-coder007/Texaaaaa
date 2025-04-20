<x-filament-panels::page.simple class="max-w-100">
    <style>
        .fi-simple-main {
            max-width: 80% !important;
        }
    </style>
    <x-slot name="heading">
        Client Registration
    </x-slot>

    <x-slot name="subheading">
        Complete the following steps to register your client account.

        <div class="mt-4">
            {{ __('filament-panels::pages/auth/register.actions.login.before') }}

            {{ $this->loginAction }}
        </div>

        <div class="mt-4 text-sm text-center">
            <p class="mb-2">Access the right portal for your role:</p>
            <div class="mt-4 flex justify-center">
                <div class="inline-flex rounded-md shadow-sm" role="group">
                    <a href="{{ route('filament.pbo.auth.register') }}"
                       class="px-4 py-2 text-sm font-medium border-t border-b border-l rounded-l-lg bg-white text-gray-700 border-gray-200 hover:bg-gray-50">
                       PBO
                    </a>
                    <a href="{{ route('filament.client.auth.register') }}"
                       class="px-4 py-2 text-sm font-medium border border-primary-600 bg-primary-500 text-white rounded-r-lg">
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

    <div class="mt-6 text-sm text-center text-gray-500">
        <p>After registration, you'll have immediate access to your client portal.</p>
        <p>Please keep your login credentials secure.</p>
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_REGISTER_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>

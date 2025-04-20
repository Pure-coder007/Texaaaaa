<x-filament-panels::page >
    <style>
        .fi-sidebar {
            display: none;
        }
    </style>
    <x-filament::section>
        <form wire:submit="submitForm">
            {{ $this->form }}
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">Need help completing your profile? <a href="/contact" class="text-primary-600 hover:text-primary-500">Contact Support</a></p>
        </div>
    </x-filament::section>
</x-filament-panels::page>

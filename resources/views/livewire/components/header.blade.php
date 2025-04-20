<header class="relative z-30">
    <!-- Main navigation -->
    <nav class="bg-white shadow-sm">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="/" class="flex-shrink-0">
                    <img src="{{ asset('logo.png') }}" alt="{{ $systemSettings->site_name }} Logo" class="h-10">
                </a>

                <!-- Desktop navigation -->
                <div class="hidden md:flex items-center space-x-1">
                    <div class="flex items-center space-x-4 text-white">
                        @auth
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center text-sm font-medium bg-white text-primary px-4 py-1.5 rounded-full hover:bg-gray-100 transition-colors">
                                    <span>{{ Auth::user()->name }}</span>
                                    <i class="ph ph-caret-down ml-1"></i>
                                </button>
                                <div x-show="open"
                                     @click.away="open = false"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 z-50">
                                    <a href="/client" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard</a>
                                    <a href="/client/my-profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                    {{-- <a href="#" wire:click="logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a> --}}
                                </div>
                            </div>
                        @else
                            <a href="/client/login" class="text-sm font-medium bg-white text-primary px-4 py-1.5 rounded-full hover:bg-gray-100 transition-colors">Login</a>
                        @endauth
                    </div>
                    @guest
                        @if($systemSettings->show_register_button)
                            <a href="/client/register" class="ml-2 px-5 py-2 bg-primary text-white rounded-full hover:bg-primary-dark transition-colors">Register</a>
                        @endif
                    @endguest
                </div>

                <!-- Mobile menu button -->
                <button wire:click="toggleMobileMenu" class="md:hidden flex items-center p-2 rounded-md text-gray-700 hover:text-primary hover:bg-primary-light/30">
                    <i class="ph-fill ph-list text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div x-data x-show="$wire.mobileMenuOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="md:hidden bg-white border-t border-gray-100">
            <div class="container mx-auto px-4 pt-2 pb-4 space-y-1">
                <div class="space-y-4 text-white">
                    @auth
                        <a  href="/client" class="text-sm block font-medium bg-white text-primary px-4 py-1.5 rounded-full hover:bg-gray-100 transition-colors">Dashboard</a>
                        <a  href="/client/my-profile" class="text-sm block font-medium bg-white text-primary px-4 py-1.5 rounded-full hover:bg-gray-100 transition-colors">Profile</a>
                    @else
                        <a href="/client/login" class="text-sm block font-medium bg-white text-primary px-4 py-1.5 rounded-full hover:bg-gray-100 transition-colors">Login</a>
                    @endauth
                </div>
                @guest
                    @if($systemSettings->show_register_button)
                        <a href="/client/register" class="ml-2 px-5 py-2 bg-primary text-white rounded-full hover:bg-primary-dark transition-colors">Register</a>
                    @endif
                @endguest
            </div>
        </div>
    </nav>
</header>
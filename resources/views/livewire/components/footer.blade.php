<footer class="bg-gray-900 text-white">
    <!-- Main Footer -->
    <div class="py-16">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <!-- Company Information -->
                <div class="col-span-1 md:col-span-1">
                    <a href="/" class="flex-shrink-0 mb-4">
                        <img src="{{ asset('logo.png') }}" alt="{{ $systemSettings->site_name }} Logo" class="h-10">
                    </a>

                    <p class="text-gray-400 mb-6 mt-4">
                        {{ $systemSettings->footer_tagline }}
                    </p>

                    <div class="flex space-x-4">
                        @if($systemSettings->social_facebook)
                        <a href="{{ $systemSettings->social_facebook }}" class="text-gray-400 hover:text-white transition-colors" aria-label="Facebook">
                            <i class="ph-fill ph-facebook-logo text-xl"></i>
                        </a>
                        @endif

                        @if($systemSettings->social_instagram)
                        <a href="{{ $systemSettings->social_instagram }}" class="text-gray-400 hover:text-white transition-colors" aria-label="Instagram">
                            <i class="ph-fill ph-instagram-logo text-xl"></i>
                        </a>
                        @endif

                        @if($systemSettings->social_twitter)
                        <a href="{{ $systemSettings->social_twitter }}" class="text-gray-400 hover:text-white transition-colors" aria-label="Twitter">
                            <i class="ph-fill ph-twitter-logo text-xl"></i>
                        </a>
                        @endif

                        @if($systemSettings->social_linkedin)
                        <a href="{{ $systemSettings->social_linkedin }}" class="text-gray-400 hover:text-white transition-colors" aria-label="LinkedIn">
                            <i class="ph-fill ph-linkedin-logo text-xl"></i>
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="/" class="text-gray-400 hover:text-white transition-colors">Home</a></li>
                        <li><a href="/estates" class="text-gray-400 hover:text-white transition-colors">Estates</a></li>
                        <li><a href="/plots" class="text-gray-400 hover:text-white transition-colors">Plots</a></li>
                        <li><a href="/agents" class="text-gray-400 hover:text-white transition-colors">Agents</a></li>
                        <li><a href="/about" class="text-gray-400 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="/contact" class="text-gray-400 hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </div>

                <!-- Locations -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Popular Cities</h3>
                    <ul class="space-y-2">
                        @foreach($popularCities as $city)
                            <li>
                                <a href="/estates?city={{ $city->id }}" class="text-gray-400 hover:text-white transition-colors">
                                    {{ $city->name }}, {{ $city->state_name }}
                                </a>
                            </li>
                        @endforeach
                        <li>
                            <a href="/estates" class="text-primary-light hover:text-white transition-colors">
                                View All Cities
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contact Information -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <i class="ph-duotone ph-map-pin text-primary-light mt-1 mr-3"></i>
                            <span class="text-gray-400">{!! $systemSettings->footer_address !!}</span>
                        </div>

                        <div class="flex items-center">
                            <i class="ph-duotone ph-phone text-primary-light mr-3"></i>
                            <span class="text-gray-400">{{ $systemSettings->footer_phone }}</span>
                        </div>

                        <div class="flex items-center">
                            <i class="ph-duotone ph-envelope-simple text-primary-light mr-3"></i>
                            <span class="text-gray-400">{{ $systemSettings->footer_email }}</span>
                        </div>

                        <div class="flex items-center">
                            <i class="ph-duotone ph-clock text-primary-light mr-3"></i>
                            <span class="text-gray-400">{{ $systemSettings->office_hours }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Copyright Section -->
    <div class="py-6 border-t border-gray-800">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-400 text-sm mb-4 md:mb-0">
                    &copy; {{ date('Y') }} {{ $systemSettings->company_legal_name }}. All rights reserved.
                </div>

                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ $systemSettings->terms_url }}" class="text-gray-400 text-sm hover:text-white transition-colors">Terms of Service</a>
                    <a href="{{ $systemSettings->privacy_url }}" class="text-gray-400 text-sm hover:text-white transition-colors">Privacy Policy</a>
                </div>
            </div>
        </div>
    </div>
</footer>
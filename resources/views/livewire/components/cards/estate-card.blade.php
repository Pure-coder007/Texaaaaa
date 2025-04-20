<div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow overflow-hidden h-full">
    <!-- Estate Image -->
    <div class="relative h-48 overflow-hidden">
        @if($estate->getFirstMediaUrl('estate_images'))
            <img src="{{ $estate->getFirstMediaUrl('estate_images') }}"
                 alt="{{ $estate->name }}"
                 class="w-full h-full object-cover">
        @else
            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                <i class="ph ph-buildings text-4xl text-gray-400"></i>
            </div>
        @endif

        <!-- Location badge -->
        <div class="absolute bottom-3 left-3 bg-white/80 backdrop-blur-sm text-gray-700 text-xs px-2 py-1 rounded-lg">
            <i class="ph ph-map-pin text-primary mr-1"></i>
            {{ $estate->city->name ?? 'Unknown Location' }}
        </div>
    </div>

    <!-- Estate Details -->
    <div class="p-5">
        <h2 class="font-bold text-xl mb-2 text-gray-800">{{ $estate->name }}</h2>

        <div class="text-gray-500 text-sm mb-3">
            {{ Str::limit($estate->description, 100) }}
        </div>

        <!-- Stats -->
        <div class="flex items-center justify-between text-sm mb-4">
            <div class="flex items-center">
                <i class="ph ph-ruler text-gray-500 mr-1"></i>
                <span>{{ number_format($estate->total_area) }} sqm</span>
            </div>

            <div class="flex items-center">
                <i class="ph ph-map-trifold text-gray-500 mr-1"></i>
                <span>{{ $estate->plots->count() }} plots</span>
            </div>

            <div class="flex items-center">
                @php
                    $availablePlotsCount = $estate->plots->where('status', 'available')->count();
                    $statusColor = $availablePlotsCount > 0 ? 'text-green-500' : 'text-red-500';
                @endphp
                <i class="ph ph-check-circle {{ $statusColor }} mr-1"></i>
                <span>{{ $availablePlotsCount }} available</span>
            </div>
        </div>

        <!-- Price range and action button -->
        <div class="flex justify-between items-center mt-auto">
            <div>
                <span class="text-xs text-gray-500">Starting from</span>
                <div class="font-bold text-lg text-primary">
                    ₦{{ number_format($estate->plots->min('price')) }}
                </div>
            </div>

            <a href="{{ route('estates.show', $estate->id) }}"
               class="bg-primary hover:bg-primary-dark text-white px-5 py-2 rounded-lg text-sm transition-colors">
                View Estate
            </a>
        </div>
    </div>
</div>

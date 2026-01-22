<x-layouts.app title="Admin Dashboard">
    <x-slot:header>
        Dashboard
    </x-slot:header>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 gap-3 mb-6">
        {{-- Total Outlets --}}
        <x-card variant="glass" class="p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Outlets</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['outlets']['total'] }}</p>
            <p class="text-xs text-gray-400 mt-1">
                {{ $stats['outlets']['active'] }} active
            </p>
        </x-card>

        {{-- Total Bins --}}
        <x-card variant="glass" class="p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Bins</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['bins']['total'] }}</p>
            <p class="text-xs text-gray-400 mt-1">
                {{ $stats['bins']['assigned'] }} assigned
            </p>
        </x-card>

        {{-- Ready for Pickup --}}
        <x-card variant="glass" class="p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Ready for Pickup</p>
            <p class="text-2xl font-bold {{ $stats['bins']['ready_for_pickup'] > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">
                {{ $stats['bins']['ready_for_pickup'] }}
            </p>
            <p class="text-xs text-gray-400 mt-1">
                bins at 80%+
            </p>
        </x-card>

        {{-- Today's Detections --}}
        <x-card variant="glass" class="p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Today</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['detections']['today'] }}</p>
            <p class="text-xs text-gray-400 mt-1">
                detections
            </p>
        </x-card>
    </div>

    {{-- Bins Needing Pickup --}}
    <div class="mb-6">
        <h2 class="text-sm font-semibold text-gray-900 mb-3">Bins Needing Pickup</h2>
        @if ($binsNeedingPickup->isEmpty())
            <x-card variant="subtle" class="text-center py-8">
                <p class="text-gray-400">No bins need pickup</p>
            </x-card>
        @else
            <div class="space-y-2">
                @foreach ($binsNeedingPickup as $bin)
                    <x-card variant="glass" class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ $bin->serial_number }}</p>
                                <p class="text-xs text-gray-500">
                                    @if ($bin->currentAssignment)
                                        {{ $bin->currentAssignment->outlet->name }}
                                    @else
                                        Unassigned
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-red-500 rounded-full" style="width: {{ $bin->fill_level }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-red-600">{{ $bin->fill_level }}%</span>
                            </div>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Recent Detections --}}
    <div class="mb-6">
        <h2 class="text-sm font-semibold text-gray-900 mb-3">Recent Detections</h2>
        @if ($recentDetections->isEmpty())
            <x-card variant="subtle" class="text-center py-8">
                <p class="text-gray-400">No detections yet</p>
            </x-card>
        @else
            <div class="space-y-2">
                @foreach ($recentDetections as $detection)
                    <x-card variant="glass" class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $detection->waste_type->value)) }}</p>
                                <p class="text-xs text-gray-500">{{ $detection->bin->serial_number }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs bg-gray-100 text-gray-600 rounded-full px-2 py-0.5">
                                    {{ $detection->confidence }}%
                                </span>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $detection->detected_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-3 gap-3">
        <x-button href="{{ route('admin.outlets.index') }}" class="justify-center">
            Outlets
        </x-button>
        <x-button href="{{ route('admin.bins.index') }}" class="justify-center">
            Bins
        </x-button>
        <x-button href="{{ route('admin.detection-events.index') }}" class="justify-center">
            Events
        </x-button>
    </div>
</x-layouts.app>

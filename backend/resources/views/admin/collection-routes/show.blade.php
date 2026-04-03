<x-layouts.admin :title="'Route #' . $collectionRoute->id">
    <x-slot:back>
        <x-back-button href="{{ route('admin.collection-routes.index') }}" />
    </x-slot:back>

    <x-slot:header>
        Collection Route #{{ $collectionRoute->id }}
    </x-slot:header>

    @php
        $statusConfig = match($collectionRoute->status) {
            \App\Enums\RouteStatus::Pending => ['bg' => 'bg-amber-100 text-amber-700'],
            \App\Enums\RouteStatus::Accepted => ['bg' => 'bg-blue-100 text-blue-700'],
            \App\Enums\RouteStatus::InProgress => ['bg' => 'bg-indigo-100 text-indigo-700'],
            \App\Enums\RouteStatus::Completed => ['bg' => 'bg-emerald-100 text-emerald-700'],
            \App\Enums\RouteStatus::Rejected => ['bg' => 'bg-red-100 text-red-700'],
        };
    @endphp

    <div class="space-y-6">
        {{-- Status + Summary --}}
        <x-card class="p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-semibold rounded-full px-4 py-1.5 {{ $statusConfig['bg'] }}">
                    {{ $collectionRoute->status->label() }}
                </span>
                <span class="text-sm text-gray-400">
                    Created {{ $collectionRoute->created_at->format('d M Y, H:i') }}
                </span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider">Collector</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $collectionRoute->collector?->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider">Distance</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $collectionRoute->total_distance_km ? number_format($collectionRoute->total_distance_km, 1) . ' km' : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider">Duration</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $collectionRoute->total_duration_min ? $collectionRoute->total_duration_min . ' min' : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider">Depot</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $collectionRoute->depot_name ?? 'N/A' }}</p>
                </div>
            </div>
            @if ($collectionRoute->started_at || $collectionRoute->completed_at)
                <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-200/60">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider">Started</p>
                        <p class="text-sm text-gray-900 mt-1">{{ $collectionRoute->started_at?->format('d M Y, H:i') ?? 'Not started' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider">Completed</p>
                        <p class="text-sm text-gray-900 mt-1">{{ $collectionRoute->completed_at?->format('d M Y, H:i') ?? 'Not completed' }}</p>
                    </div>
                </div>
            @endif
        </x-card>

        {{-- Route Stops --}}
        <x-card class="p-6">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-6 flex items-center gap-1.5">
                <x-heroicon-o-map-pin class="w-4 h-4" />
                Route Stops ({{ $collectionRoute->routeStops->count() }})
            </h2>

            @if ($collectionRoute->routeStops->isEmpty())
                <p class="text-sm text-gray-400 text-center py-8">No stops on this route</p>
            @else
                <div class="relative pl-10">
                    <div class="absolute left-[11px] top-1 bottom-1 w-0.5 bg-gray-200"></div>

                    @foreach ($collectionRoute->routeStops as $stop)
                        @php
                            $stopStatus = match($stop->status) {
                                'completed' => ['dot' => 'bg-emerald-100 border-emerald-400', 'inner' => 'bg-emerald-500'],
                                'skipped' => ['dot' => 'bg-red-100 border-red-400', 'inner' => 'bg-red-500'],
                                default => ['dot' => 'bg-gray-100 border-gray-300', 'inner' => 'bg-gray-300'],
                            };
                        @endphp
                        <div class="relative mb-6 last:mb-0">
                            <div class="absolute -left-10 w-6 h-6 rounded-full {{ $stopStatus['dot'] }} border-2 flex items-center justify-center">
                                @if ($stop->status === 'completed')
                                    <x-heroicon-s-check class="w-3 h-3 text-emerald-600" />
                                @else
                                    <div class="w-2 h-2 rounded-full {{ $stopStatus['inner'] }}"></div>
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <p class="font-medium text-gray-900">
                                        Stop {{ $stop->stop_order }}
                                        <span class="text-gray-400 font-normal">&mdash;</span>
                                        {{ $stop->bin?->serial_number ?? 'Unknown bin' }}
                                    </p>
                                    <span class="text-xs font-medium rounded-full px-2 py-0.5 {{ match($stop->status) {
                                        'completed' => 'bg-emerald-100 text-emerald-700',
                                        'skipped' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    } }}">
                                        {{ ucfirst($stop->status ?? 'pending') }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mt-0.5">{{ $stop->address ?? 'No address' }}</p>
                                <div class="text-xs text-gray-400 mt-1 flex items-center gap-3">
                                    @if ($stop->distance_km)
                                        <span>{{ number_format($stop->distance_km, 1) }} km</span>
                                    @endif
                                    @if ($stop->duration_min)
                                        <span>{{ $stop->duration_min }} min</span>
                                    @endif
                                    @if ($stop->eta)
                                        <span>ETA: {{ $stop->eta->format('H:i') }}</span>
                                    @endif
                                    @if ($stop->completed_at)
                                        <span>Done: {{ $stop->completed_at->format('H:i') }}</span>
                                    @endif
                                </div>
                                @if ($stop->skip_reason)
                                    <p class="text-xs text-red-500 mt-1">Skipped: {{ $stop->skip_reason }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.admin>

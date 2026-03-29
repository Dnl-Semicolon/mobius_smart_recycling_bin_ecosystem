<x-layouts.agency title="Agency Dashboard">
    <x-slot:header>Dashboard</x-slot:header>

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['label' => 'Total Collectors', 'value' => $totalCollectors, 'icon' => 'heroicon-o-users', 'color' => 'indigo'],
            ['label' => 'Active Routes', 'value' => $activeRoutes, 'icon' => 'heroicon-o-map', 'color' => 'blue'],
            ['label' => 'Completed This Week', 'value' => $completedThisWeek, 'icon' => 'heroicon-o-check-circle', 'color' => 'emerald'],
            ['label' => 'Total Completed', 'value' => $totalCompleted, 'icon' => 'heroicon-o-trophy', 'color' => 'amber'],
        ] as $stat)
            <x-card class="p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-{{ $stat['color'] }}-100 flex items-center justify-center shrink-0">
                        <x-dynamic-component :component="$stat['icon']" class="w-5 h-5 text-{{ $stat['color'] }}-600" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $stat['value'] }}</p>
                        <p class="text-xs text-gray-500">{{ $stat['label'] }}</p>
                    </div>
                </div>
            </x-card>
        @endforeach
    </div>

    {{-- Recent Routes --}}
    <x-card class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-700">Recent Routes</h2>
            <a href="{{ route('agency.routes.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">View all</a>
        </div>

        @if ($recentRoutes->isEmpty())
            <p class="text-sm text-gray-400 text-center py-8">No routes yet</p>
        @else
            <div class="space-y-3">
                @foreach ($recentRoutes as $route)
                    @php
                        $statusColors = [
                            'pending' => 'bg-gray-100 text-gray-600',
                            'accepted' => 'bg-blue-100 text-blue-700',
                            'active' => 'bg-indigo-100 text-indigo-700',
                            'completed' => 'bg-emerald-100 text-emerald-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                        ];
                    @endphp
                    <div class="flex items-center gap-3 py-2">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColors[$route->status->value] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $route->status->value }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 truncate">
                                {{ $route->collector?->name ?? 'Unassigned' }}
                                &middot; {{ count($route->stops ?? []) }} stops
                            </p>
                        </div>
                        <span class="text-xs text-gray-400 shrink-0">{{ $route->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>
</x-layouts.agency>

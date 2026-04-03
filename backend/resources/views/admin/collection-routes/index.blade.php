<x-layouts.admin title="Collection Routes">
    <x-slot:header>
        Collection Routes
    </x-slot:header>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.collection-routes.index') }}" class="mb-6">
        <div class="flex flex-wrap gap-2">
            <select
                name="status"
                class="rounded-xl border-gray-200 text-sm focus:border-emerald-400 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"
            >
                <option value="">All Statuses</option>
                @foreach (\App\Enums\RouteStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            <x-button type="submit" variant="secondary">
                <x-heroicon-o-funnel class="w-4 h-4" />
                Filter
            </x-button>
            @if (request()->hasAny(['status']))
                <x-button href="{{ route('admin.collection-routes.index') }}" variant="ghost">
                    Clear
                </x-button>
            @endif
        </div>
    </form>

    @if ($routes->isEmpty())
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-map class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            @if (request()->hasAny(['status']))
                <p class="text-gray-400">No routes match your filters</p>
                <a href="{{ route('admin.collection-routes.index') }}" class="inline-flex items-center gap-1.5 mt-4 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                    Clear filters
                </a>
            @else
                <p class="text-gray-400">No collection routes yet</p>
                <p class="text-sm text-gray-400 mt-1">Routes are created when pickup requests are dispatched to collectors.</p>
            @endif
        </x-card>
    @else
        <div class="space-y-3">
            @foreach ($routes as $route)
                @php
                    $statusConfig = match($route->status) {
                        \App\Enums\RouteStatus::Pending => ['bg' => 'bg-amber-100 text-amber-700', 'border' => 'border-l-amber-400'],
                        \App\Enums\RouteStatus::Accepted => ['bg' => 'bg-blue-100 text-blue-700', 'border' => 'border-l-blue-400'],
                        \App\Enums\RouteStatus::InProgress => ['bg' => 'bg-indigo-100 text-indigo-700', 'border' => 'border-l-indigo-400'],
                        \App\Enums\RouteStatus::Completed => ['bg' => 'bg-emerald-100 text-emerald-700', 'border' => 'border-l-emerald-400'],
                        \App\Enums\RouteStatus::Rejected => ['bg' => 'bg-red-100 text-red-700', 'border' => 'border-l-red-400'],
                    };
                    $stopCount = $route->routeStops->count();
                    $completedStops = $route->routeStops->where('status', 'completed')->count();
                @endphp
                <x-card
                    :href="route('admin.collection-routes.show', $route)"
                    :interactive="true"
                    class="p-5 border-l-4 {{ $statusConfig['border'] }}"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="w-11 h-11 rounded-xl bg-gray-50 flex items-center justify-center shrink-0">
                                <x-heroicon-o-map class="w-6 h-6 text-gray-400" />
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 text-base">Route #{{ $route->id }}</p>
                                <p class="text-sm text-gray-500 mt-0.5 flex items-center gap-1">
                                    <x-heroicon-o-user class="w-3.5 h-3.5" />
                                    {{ $route->collector?->name ?? 'Unassigned' }}
                                </p>
                                <div class="text-xs text-gray-400 mt-1 flex items-center gap-3">
                                    <span>{{ $stopCount }} stops</span>
                                    @if ($route->total_distance_km)
                                        <span>{{ number_format($route->total_distance_km, 1) }} km</span>
                                    @endif
                                    @if ($route->total_duration_min)
                                        <span>{{ $route->total_duration_min }} min</span>
                                    @endif
                                    @if ($stopCount > 0)
                                        <span>{{ $completedStops }}/{{ $stopCount }} done</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="shrink-0 flex flex-col items-end gap-2">
                            <span class="text-xs font-semibold rounded-full px-3 py-1 {{ $statusConfig['bg'] }}">
                                {{ $route->status->label() }}
                            </span>
                            <span class="text-xs text-gray-400">
                                {{ $route->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $routes->links() }}
        </div>
    @endif
</x-layouts.admin>

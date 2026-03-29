<x-layouts.agency title="Route History">
    <x-slot:header>Route History</x-slot:header>

    {{-- Filters --}}
    <form method="GET" action="{{ route('agency.routes.index') }}" class="flex gap-2 mb-6">
        <select name="collector" class="rounded-xl border-gray-200 text-sm focus:border-indigo-400 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <option value="">All Collectors</option>
            @foreach ($collectors as $collector)
                <option value="{{ $collector->id }}" @selected(request('collector') == $collector->id)>
                    {{ $collector->name }}
                </option>
            @endforeach
        </select>
        <select name="status" class="rounded-xl border-gray-200 text-sm focus:border-indigo-400 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <option value="">All Statuses</option>
            @foreach (\App\Enums\RouteStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                    {{ ucfirst($status->value) }}
                </option>
            @endforeach
        </select>
        <x-button type="submit" variant="secondary">
            <x-heroicon-o-funnel class="w-4 h-4" />
            Filter
        </x-button>
    </form>

    @if ($routes->isEmpty())
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-map class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-400">No routes found</p>
        </x-card>
    @else
        <div class="space-y-3">
            @foreach ($routes as $route)
                @php
                    $statusColors = [
                        'pending' => 'bg-gray-100 text-gray-600',
                        'accepted' => 'bg-blue-100 text-blue-700',
                        'active' => 'bg-indigo-100 text-indigo-700',
                        'completed' => 'bg-emerald-100 text-emerald-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                    ];
                @endphp
                <a href="{{ route('agency.routes.show', $route) }}" class="block">
                    <x-card class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColors[$route->status->value] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($route->status->value) }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $route->collector?->name ?? 'Unassigned' }}
                                </p>
                                <p class="text-xs text-gray-500">{{ count($route->stops ?? []) }} stops &middot; {{ number_format($route->total_distance ?? 0, 1) }} km</p>
                            </div>
                            <span class="text-xs text-gray-400 shrink-0">{{ $route->created_at->diffForHumans() }}</span>
                        </div>
                    </x-card>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $routes->withQueryString()->links() }}
        </div>
    @endif
</x-layouts.agency>

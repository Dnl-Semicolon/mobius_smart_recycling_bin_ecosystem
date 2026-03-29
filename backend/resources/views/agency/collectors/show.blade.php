<x-layouts.agency title="{{ $user->name }}">
    <x-slot:header>{{ $user->name }}</x-slot:header>
    <x-slot:back>
        <a href="{{ route('agency.collectors.index') }}" class="topbar-icon-btn" title="Back">
            <x-heroicon-o-arrow-left class="w-5 h-5" />
        </a>
    </x-slot:back>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Stats --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <x-card class="p-4">
                    <p class="text-2xl font-bold text-gray-900">{{ $routeStats['completed'] }}</p>
                    <p class="text-xs text-gray-500">Routes Completed</p>
                </x-card>
                <x-card class="p-4">
                    <p class="text-2xl font-bold text-gray-900">{{ $routeStats['active'] }}</p>
                    <p class="text-xs text-gray-500">Active Routes</p>
                </x-card>
            </div>

            {{-- Recent routes --}}
            <x-card class="p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Route History</h2>
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
                                <p class="text-sm text-gray-900 flex-1">{{ count($route->stops ?? []) }} stops</p>
                                <span class="text-xs text-gray-400">{{ $route->created_at->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>
        </div>

        {{-- Profile card + actions --}}
        <div class="space-y-6">
            <x-card class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="text-lg font-semibold text-indigo-700">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">{{ $user->name }}</p>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    </div>
                </div>
                @if ($user->phone)
                    <p class="text-sm text-gray-500"><span class="font-medium text-gray-700">Phone:</span> {{ $user->phone }}</p>
                @endif
            </x-card>

            <x-card class="p-6">
                <form method="POST" action="{{ route('agency.collectors.remove', $user) }}">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="secondary" class="w-full justify-center text-red-600 hover:bg-red-50" onclick="return confirm('Remove this collector from your agency?')">
                        <x-heroicon-o-user-minus class="w-4 h-4" />
                        Remove from Agency
                    </x-button>
                </form>
            </x-card>
        </div>
    </div>
</x-layouts.agency>

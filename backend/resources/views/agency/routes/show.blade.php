<x-layouts.agency title="Route Details">
    <x-slot:header>Route #{{ $route->id }}</x-slot:header>
    <x-slot:back>
        <a href="{{ route('agency.routes.index') }}" class="topbar-icon-btn" title="Back">
            <x-heroicon-o-arrow-left class="w-5 h-5" />
        </a>
    </x-slot:back>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Route info --}}
            <x-card class="p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Route Information</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Collector</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $route->collector?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd class="mt-0.5">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-gray-100 text-gray-600',
                                    'accepted' => 'bg-blue-100 text-blue-700',
                                    'active' => 'bg-indigo-100 text-indigo-700',
                                    'completed' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColors[$route->status->value] ?? '' }}">
                                {{ ucfirst($route->status->value) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Total Distance</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ number_format($route->total_distance ?? 0, 1) }} km</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Stops</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ count($route->stops ?? []) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Created</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $route->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Updated</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $route->updated_at->format('d M Y, H:i') }}</dd>
                    </div>
                </dl>
            </x-card>

            {{-- Stops --}}
            @if (!empty($route->stops))
                <x-card class="p-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Stops</h2>
                    <div class="space-y-3">
                        @foreach ($route->stops as $i => $stop)
                            <div class="flex items-center gap-3 py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                                <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                                    <span class="text-xs font-bold text-indigo-700">{{ $i + 1 }}</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900">
                                        Bin #{{ $stop['bin_id'] ?? '?' }}
                                    </p>
                                    @if (isset($stop['eta']))
                                        <p class="text-xs text-gray-500">ETA: {{ $stop['eta'] }}</p>
                                    @endif
                                </div>
                                @php
                                    $stopStatus = $stop['status'] ?? 'pending';
                                    $stopColors = [
                                        'pending' => 'text-gray-400',
                                        'completed' => 'text-emerald-500',
                                        'skipped' => 'text-amber-500',
                                    ];
                                @endphp
                                <x-heroicon-s-check-circle class="w-5 h-5 {{ $stopColors[$stopStatus] ?? 'text-gray-400' }}" />
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>

        <div>
            <x-card class="p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Collector</h2>
                @if ($route->collector)
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                            <span class="text-sm font-semibold text-indigo-700">{{ strtoupper(substr($route->collector->name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $route->collector->name }}</p>
                            <p class="text-xs text-gray-500">{{ $route->collector->email }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-400">No collector assigned</p>
                @endif
            </x-card>
        </div>
    </div>
</x-layouts.agency>

<x-layouts.agency title="Collectors">
    <x-slot:header>Collectors</x-slot:header>

    {{-- Invite form --}}
    <x-card class="p-4 mb-6">
        <form method="POST" action="{{ route('agency.collectors.invite') }}" class="flex gap-3">
            @csrf
            <x-input
                name="email"
                type="email"
                placeholder="Invite a collector by email..."
                required
                class="flex-1"
            />
            <x-button type="submit">
                <x-heroicon-o-paper-airplane class="w-4 h-4" />
                Invite
            </x-button>
        </form>
    </x-card>

    @if ($collectors->isEmpty())
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-users class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-400">No collectors yet. Invite your first collector above.</p>
        </x-card>
    @else
        <div class="space-y-3">
            @foreach ($collectors as $collector)
                <a href="{{ route('agency.collectors.show', $collector) }}" class="block">
                    <x-card class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                                <span class="text-sm font-semibold text-indigo-700">{{ strtoupper(substr($collector->name, 0, 1)) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $collector->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $collector->email }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-medium text-gray-900">{{ $collector->routes_completed ?? 0 }}</p>
                                <p class="text-xs text-gray-500">routes</p>
                            </div>
                            @php
                                $pivotStatus = $collector->pivot->status;
                                $pivotColors = [
                                    'active' => 'bg-emerald-100 text-emerald-700',
                                    'invited' => 'bg-amber-100 text-amber-700',
                                    'removed' => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $pivotColors[$pivotStatus] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($pivotStatus) }}
                            </span>
                        </div>
                    </x-card>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.agency>

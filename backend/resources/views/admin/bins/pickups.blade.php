<x-layouts.admin :title="$bin->serial_number . ' — Pickups'">
    <x-slot:back>
        <x-back-button href="{{ route('admin.bins.show', $bin) }}" />
    </x-slot:back>

    <x-slot:header>
        {{ $bin->serial_number }}
    </x-slot:header>

    {{-- Hero Bar (compact) --}}
    <x-card class="!p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <x-bin-gauge :value="$bin->fill_level" :size="48" />
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $bin->serial_number }}</p>
                    <p class="text-xs text-gray-500">{{ $bin->currentAssignment?->outlet->name ?? 'Unassigned' }}</p>
                </div>
            </div>
            <x-bin-tab-nav :bin="$bin" active="pickups" />
        </div>
    </x-card>

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <x-card variant="glass" class="!p-4">
            <p class="text-xs text-gray-400">Total Pickups</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        </x-card>
        <x-card variant="glass" class="!p-4">
            <p class="text-xs text-gray-400">Completed</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $stats['completed'] }}</p>
        </x-card>
        <x-card variant="glass" class="!p-4">
            <p class="text-xs text-gray-400">Avg Response</p>
            <p class="text-2xl font-bold text-gray-900">
                @if ($stats['avg_response_minutes'] > 0)
                    {{ $stats['avg_response_minutes'] }}m
                @else
                    —
                @endif
            </p>
        </x-card>
        <x-card variant="glass" class="!p-4">
            <p class="text-xs text-gray-400">Last Collected</p>
            <p class="text-sm font-semibold text-gray-900 mt-1">
                @if ($stats['last_collected'])
                    {{ \Carbon\Carbon::parse($stats['last_collected'])->diffForHumans() }}
                @else
                    Never
                @endif
            </p>
        </x-card>
    </div>

    @if ($pickups->isEmpty())
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-truck class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-400 text-lg">No pickup requests</p>
            <p class="text-sm text-gray-400 mt-1">Pickup requests are auto-created when a bin reaches 80% fill.</p>
        </x-card>
    @else
        <div class="space-y-4">
            @foreach ($pickups as $pickup)
                @php
                    $statusConfig = match($pickup->status) {
                        \App\Enums\PickupStatus::Pending => ['bg' => 'bg-amber-100 text-amber-700', 'border' => 'border-l-amber-400'],
                        \App\Enums\PickupStatus::Claimed => ['bg' => 'bg-blue-100 text-blue-700', 'border' => 'border-l-blue-400'],
                        \App\Enums\PickupStatus::Completed => ['bg' => 'bg-emerald-100 text-emerald-700', 'border' => 'border-l-emerald-400'],
                        default => ['bg' => 'bg-gray-100 text-gray-600', 'border' => 'border-l-gray-400'],
                    };
                @endphp
                <x-card
                    :href="route('admin.pickup-requests.show', $pickup)"
                    :interactive="true"
                    class="!p-5 border-l-4 {{ $statusConfig['border'] }}"
                >
                    {{-- Timeline-style layout --}}
                    <div class="flex items-start justify-between mb-3">
                        <span class="text-xs font-semibold rounded-full px-3 py-1 {{ $statusConfig['bg'] }}">
                            {{ $pickup->status->label() }}
                        </span>
                        <span class="text-xs text-gray-400">#{{ $pickup->id }}</span>
                    </div>

                    <div class="relative pl-6">
                        <div class="absolute left-[5px] top-1 bottom-1 w-0.5 bg-gray-200"></div>

                        {{-- Created --}}
                        <div class="relative mb-3">
                            <div class="absolute -left-6 w-3 h-3 rounded-full bg-amber-400 border-2 border-white mt-0.5"></div>
                            <p class="text-sm text-gray-700">Requested {{ $pickup->created_at->format('d M, H:i') }}</p>
                        </div>

                        {{-- Claimed --}}
                        @if ($pickup->claimed_at)
                            <div class="relative mb-3">
                                <div class="absolute -left-6 w-3 h-3 rounded-full bg-blue-400 border-2 border-white mt-0.5"></div>
                                <p class="text-sm text-gray-700">
                                    Claimed by {{ $pickup->claimedBy?->name ?? 'Unknown' }}
                                    <span class="text-gray-400">{{ $pickup->claimed_at->format('d M, H:i') }}</span>
                                </p>
                            </div>
                        @endif

                        {{-- Completed --}}
                        @if ($pickup->completed_at)
                            <div class="relative">
                                <div class="absolute -left-6 w-3 h-3 rounded-full bg-emerald-400 border-2 border-white mt-0.5"></div>
                                <p class="text-sm text-gray-700">
                                    Completed
                                    <span class="text-gray-400">{{ $pickup->completed_at->format('d M, H:i') }}</span>
                                </p>
                            </div>
                        @endif
                    </div>
                </x-card>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $pickups->links() }}
        </div>
    @endif
</x-layouts.admin>

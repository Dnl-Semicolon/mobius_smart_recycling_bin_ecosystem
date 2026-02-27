<x-layouts.admin title="Pickup Requests">
    <x-slot:header>
        Pickup Requests
    </x-slot:header>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.pickup-requests.index') }}" class="mb-6">
        <div class="flex flex-wrap gap-2">
            <select
                name="status"
                class="rounded-xl border-gray-200 text-sm focus:border-emerald-400 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"
            >
                <option value="">All Statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                        {{ ucfirst($status->value) }}
                    </option>
                @endforeach
            </select>
            <x-button type="submit" variant="secondary">
                <x-heroicon-o-funnel class="w-4 h-4" />
                Filter
            </x-button>
            @if (request()->hasAny(['status', 'bin']))
                <x-button href="{{ route('admin.pickup-requests.index') }}" variant="ghost">
                    Clear
                </x-button>
            @endif
        </div>
    </form>

    @if ($pickupRequests->isEmpty())
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-truck class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-400 text-lg">No pickup requests found</p>
            <p class="text-sm text-gray-400 mt-1">Pickup requests are automatically created when a bin reaches 80% fill.</p>
        </x-card>
    @else
        <div class="space-y-3">
            @foreach ($pickupRequests as $pickupRequest)
                @php
                    $bin = $pickupRequest->bin;
                    $outlet = $bin->currentAssignment?->outlet;
                    $statusConfig = match($pickupRequest->status) {
                        \App\Enums\PickupStatus::Pending => ['bg' => 'bg-amber-100 text-amber-700', 'icon' => 'clock', 'border' => 'border-l-amber-400'],
                        \App\Enums\PickupStatus::Claimed => ['bg' => 'bg-blue-100 text-blue-700', 'icon' => 'hand-raised', 'border' => 'border-l-blue-400'],
                        \App\Enums\PickupStatus::Completed => ['bg' => 'bg-emerald-100 text-emerald-700', 'icon' => 'check-circle', 'border' => 'border-l-emerald-400'],
                    };
                @endphp
                <x-card
                    :href="route('admin.pickup-requests.show', $pickupRequest)"
                    :interactive="true"
                    class="p-5 border-l-4 {{ $statusConfig['border'] }}"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="w-11 h-11 rounded-xl bg-gray-50 flex items-center justify-center shrink-0">
                                <x-heroicon-s-archive-box class="w-6 h-6 text-gray-400" />
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 text-base">{{ $bin->serial_number }}</p>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    {{ $outlet?->name ?? 'Unassigned' }}
                                </p>
                                @if ($pickupRequest->claimedBy)
                                    <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                                        <x-heroicon-o-user class="w-3 h-3" />
                                        {{ $pickupRequest->claimedBy->name }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="shrink-0 flex flex-col items-end gap-2">
                            <span class="text-xs font-semibold rounded-full px-3 py-1 {{ $statusConfig['bg'] }}">
                                {{ ucfirst($pickupRequest->status->value) }}
                            </span>
                            <span class="text-xs text-gray-400">
                                {{ $pickupRequest->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $pickupRequests->links() }}
        </div>
    @endif
</x-layouts.admin>

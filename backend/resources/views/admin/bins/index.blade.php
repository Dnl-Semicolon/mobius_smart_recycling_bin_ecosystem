<x-layouts.admin title="Bins">
    <x-slot:header>
        Bins
    </x-slot:header>

    <x-slot:actions>
        <x-button href="{{ route('admin.bins.create') }}">
            Add
        </x-button>
    </x-slot:actions>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 p-4 rounded-xl bg-green-50 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.bins.index') }}" class="mb-6">
        <div class="space-y-3">
            <div class="flex gap-2">
                <x-input
                    name="search"
                    type="text"
                    placeholder="Search serial number..."
                    :value="request('search')"
                    class="flex-1"
                />
                <x-button type="submit">
                    Filter
                </x-button>
            </div>
            <div class="flex gap-2 flex-wrap">
                <select
                    name="status"
                    class="rounded-xl border-gray-200 text-sm focus:border-gray-300 focus:ring focus:ring-gray-200 focus:ring-opacity-50"
                >
                    <option value="">All Status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                            {{ ucfirst($status->value) }}
                        </option>
                    @endforeach
                </select>
                <select
                    name="outlet"
                    class="rounded-xl border-gray-200 text-sm focus:border-gray-300 focus:ring focus:ring-gray-200 focus:ring-opacity-50"
                >
                    <option value="">All Outlets</option>
                    @foreach ($outlets as $outlet)
                        <option value="{{ $outlet->id }}" @selected(request('outlet') == $outlet->id)>
                            {{ $outlet->name }}
                        </option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input
                        type="checkbox"
                        name="ready_for_pickup"
                        value="1"
                        @checked(request('ready_for_pickup'))
                        class="rounded border-gray-300 text-gray-900 focus:ring-gray-200"
                        onchange="this.form.submit()"
                    >
                    Ready for pickup
                </label>
            </div>
        </div>
    </form>

    @if ($bins->isEmpty())
        {{-- Empty State --}}
        <x-card variant="subtle" class="text-center py-16">
            <p class="text-gray-400 mb-6">No bins found</p>
            <x-button href="{{ route('admin.bins.create') }}">
                Create your first bin
            </x-button>
        </x-card>
    @else
        {{-- Bin List --}}
        <div class="space-y-3">
            @foreach ($bins as $bin)
                @php
                    $isReadyForPickup = $bin->status === \App\Enums\BinStatus::Active && $bin->fill_level >= 80;
                    $fillColor = match(true) {
                        $bin->fill_level >= 80 => 'bg-red-500',
                        $bin->fill_level >= 50 => 'bg-yellow-500',
                        default => 'bg-green-500',
                    };
                    $statusColors = [
                        'active' => 'bg-green-100 text-green-700',
                        'inactive' => 'bg-gray-100 text-gray-600',
                        'maintenance' => 'bg-orange-100 text-orange-700',
                    ];
                @endphp
                <x-card
                    :href="route('admin.bins.show', $bin)"
                    :interactive="true"
                    class="p-5 {{ $isReadyForPickup ? 'ring-2 ring-red-200' : '' }}"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h2 class="font-semibold text-gray-900">{{ $bin->serial_number }}</h2>
                                @if ($isReadyForPickup)
                                    <span class="text-xs bg-red-100 text-red-700 rounded-full px-2 py-0.5">
                                        Pickup
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 mt-0.5">
                                @if ($bin->currentAssignment)
                                    {{ $bin->currentAssignment->outlet->name }}
                                @else
                                    <span class="text-gray-400">Unassigned</span>
                                @endif
                            </p>
                        </div>
                        <div class="shrink-0 flex flex-col items-end gap-2">
                            <span class="text-xs rounded-full px-2.5 py-1 {{ $statusColors[$bin->status->value] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($bin->status->value) }}
                            </span>
                            <div class="flex items-center gap-2">
                                <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full {{ $fillColor }} rounded-full" style="width: {{ $bin->fill_level }}%"></div>
                                </div>
                                <span class="text-sm font-medium {{ $bin->fill_level >= 80 ? 'text-red-600' : 'text-gray-600' }}">
                                    {{ $bin->fill_level }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $bins->links() }}
        </div>
    @endif
</x-layouts.admin>

<x-layouts.app title="Outlets">
    <x-slot:back>
        <x-back-button href="{{ route('admin.dashboard') }}" />
    </x-slot:back>

    <x-slot:header>
        Outlets
    </x-slot:header>

    <x-slot:actions>
        <x-button href="{{ route('admin.outlets.create') }}">
            Add
        </x-button>
    </x-slot:actions>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.outlets.index') }}" class="mb-6">
        <div class="flex gap-2">
            <x-input
                name="search"
                type="text"
                placeholder="Search name or address..."
                :value="request('search')"
                class="flex-1"
            />
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
            <x-button type="submit">
                Filter
            </x-button>
        </div>
    </form>

    @if ($outlets->isEmpty())
        {{-- Empty State --}}
        <x-card variant="subtle" class="text-center py-16">
            <p class="text-gray-400 mb-6">No outlets found</p>
            <x-button href="{{ route('admin.outlets.create') }}">
                Create your first outlet
            </x-button>
        </x-card>
    @else
        {{-- Outlet List --}}
        <div class="space-y-3">
            @foreach ($outlets as $outlet)
                <x-card :href="route('admin.outlets.show', $outlet)" :interactive="true" class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-gray-900 truncate">{{ $outlet->name }}</h2>
                            <p class="text-sm text-gray-500 mt-0.5 truncate">{{ $outlet->address }}</p>
                            @if ($outlet->contact_name)
                                <p class="text-xs text-gray-400 mt-1">{{ $outlet->contact_name }}</p>
                            @endif
                        </div>
                        <div class="shrink-0 flex flex-col items-end gap-1">
                            @php
                                $statusColors = [
                                    'active' => 'bg-green-100 text-green-700',
                                    'inactive' => 'bg-gray-100 text-gray-600',
                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                ];
                            @endphp
                            <span class="text-xs rounded-full px-2.5 py-1 {{ $statusColors[$outlet->contract_status->value] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($outlet->contract_status->value) }}
                            </span>
                            <span class="text-xs text-gray-400">
                                {{ $outlet->current_bins_count }} {{ Str::plural('bin', $outlet->current_bins_count) }}
                            </span>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $outlets->links() }}
        </div>
    @endif
</x-layouts.app>

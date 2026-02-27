<x-layouts.admin title="Outlets">
    <x-slot:header>
        Outlets
    </x-slot:header>

    <x-slot:actions>
        <x-button href="{{ route('admin.outlets.create') }}">
            <x-heroicon-o-plus class="w-4 h-4" />
            Add
        </x-button>
    </x-slot:actions>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 text-emerald-700 text-sm flex items-center gap-2">
            <x-heroicon-o-check-circle class="w-5 h-5 shrink-0" />
            {{ session('success') }}
        </div>
    @endif

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
                class="rounded-xl border-gray-200 text-sm focus:border-emerald-400 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"
            >
                <option value="">All Status</option>
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
        </div>
    </form>

    @if ($outlets->isEmpty())
        {{-- Empty State --}}
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-building-storefront class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-400 mb-6">No outlets found</p>
            <x-button href="{{ route('admin.outlets.create') }}">
                <x-heroicon-o-plus class="w-4 h-4" />
                Create your first outlet
            </x-button>
        </x-card>
    @else
        {{-- Outlet List --}}
        <div class="space-y-3">
            @foreach ($outlets as $outlet)
                <x-card :href="route('admin.outlets.show', $outlet)" :interactive="true" class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0 mt-0.5">
                                <x-heroicon-s-building-storefront class="w-5 h-5 text-blue-600" />
                            </div>
                            <div class="min-w-0">
                                <h2 class="font-semibold text-gray-900 truncate">{{ $outlet->name }}</h2>
                                <p class="text-sm text-gray-500 mt-0.5 truncate">{{ $outlet->address }}</p>
                                @if ($outlet->contact_name)
                                    <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                                        <x-heroicon-o-user class="w-3 h-3" />
                                        {{ $outlet->contact_name }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="shrink-0 flex flex-col items-end gap-1.5">
                            @php
                                $statusColors = [
                                    'active' => 'bg-emerald-100 text-emerald-700',
                                    'inactive' => 'bg-gray-100 text-gray-600',
                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                ];
                            @endphp
                            <span class="text-xs font-medium rounded-full px-2.5 py-1 {{ $statusColors[$outlet->contract_status->value] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($outlet->contract_status->value) }}
                            </span>
                            <span class="text-xs text-gray-400 flex items-center gap-1">
                                <x-heroicon-o-archive-box class="w-3 h-3" />
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
</x-layouts.admin>

<x-layouts.admin title="Outlets">
    <x-slot:header>
        Outlets
    </x-slot:header>

    <div x-data="{ view: localStorage.getItem('outlet-view') || 'row' }" x-init="$watch('view', v => localStorage.setItem('outlet-view', v))">

        {{-- Page header with action --}}
        <div class="flex items-center justify-between mb-4">
            <div></div>
            <a href="{{ route('admin.outlets.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.2),0_1px_2px_rgba(0,0,0,0.15)] hover:bg-emerald-700 active:bg-emerald-800 active:shadow-[inset_0_1px_3px_rgba(0,0,0,0.2)] transition-all duration-100">
                <x-heroicon-o-building-storefront class="w-4 h-4 opacity-80" />
                New
            </a>
        </div>

        {{-- Search --}}
        @php
            $totalOutlets = array_sum($statusCounts);
            $currentStatus = request('status', '');
            $currentSearch = request('search', '');
        @endphp
        <form method="GET" action="{{ route('admin.outlets.index') }}" class="mb-4">
            @if ($currentStatus)
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif
            <div class="relative" x-data="{ focused: false }">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                </div>
                <input
                    id="outletSearchInput"
                    name="search"
                    type="text"
                    placeholder="Search by name or address..."
                    value="{{ $currentSearch }}"
                    autocomplete="off"
                    x-on:focus="focused = true"
                    x-on:blur="focused = false"
                    class="w-full rounded-xl border border-gray-200/80 bg-white/60 pl-11 pr-20 py-3 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition-all duration-150 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 focus:shadow-emerald-500/5"
                >
                <div class="absolute inset-y-0 right-3 flex items-center gap-2">
                    <kbd
                        x-show="!focused"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-90"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-90"
                        class="hidden sm:inline-flex items-center gap-0.5 rounded-md border border-gray-200 bg-gray-50 px-1.5 py-0.5 text-[11px] font-medium text-gray-400 shadow-sm"
                        title="Press / to search"
                    >/</kbd>
                    <button type="submit" class="rounded-lg bg-emerald-600 p-1.5 text-white shadow-sm hover:bg-emerald-700 active:bg-emerald-800 transition-colors cursor-pointer" title="Search">
                        <x-heroicon-s-arrow-right class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </form>

        @once
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var searchInput = document.getElementById('outletSearchInput');
            if (!searchInput) return;

            document.addEventListener('keydown', function (e) {
                if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName) && !document.activeElement.isContentEditable) {
                    e.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                }
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                }
                if (e.key === 'Escape' && document.activeElement === searchInput) {
                    searchInput.blur();
                }
            });
        });
        </script>
        @endonce

        {{-- Status filter pills + View toggle --}}
        @php
            $pillColors = [
                'active' => [
                    'dot' => 'bg-emerald-500',
                    'dotRing' => 'ring-4 ring-emerald-500/20',
                    'on' => 'bg-emerald-50 text-emerald-700 border-emerald-200 shadow-sm shadow-emerald-500/10',
                    'badge' => 'bg-emerald-100 text-emerald-600',
                ],
                'pending' => [
                    'dot' => 'bg-amber-500',
                    'dotRing' => 'ring-4 ring-amber-500/20',
                    'on' => 'bg-amber-50 text-amber-700 border-amber-200 shadow-sm shadow-amber-500/10',
                    'badge' => 'bg-amber-100 text-amber-600',
                ],
                'inactive' => [
                    'dot' => 'bg-gray-400',
                    'dotRing' => 'ring-4 ring-gray-400/20',
                    'on' => 'bg-gray-100 text-gray-700 border-gray-300 shadow-sm',
                    'badge' => 'bg-gray-200 text-gray-500',
                ],
            ];
        @endphp

        <div class="flex items-center justify-between gap-4 flex-wrap mb-6">
            {{-- Filter pills --}}
            <div class="flex items-center gap-2.5 flex-wrap">
                {{-- "All" pill --}}
                <a
                    href="{{ route('admin.outlets.index', array_filter(['search' => $currentSearch])) }}"
                    class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium border {{ $currentStatus === '' ? 'bg-gray-900 text-white border-gray-900 shadow-sm' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 shadow-sm' }}"
                >
                    All
                    <span class="text-[11px] font-semibold rounded-full min-w-[20px] px-1.5 py-0.5 text-center leading-none {{ $currentStatus === '' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $totalOutlets }}</span>
                </a>

                @foreach ($statuses as $s)
                    @php
                        $c = $pillColors[$s->value] ?? $pillColors['inactive'];
                        $count = $statusCounts[$s->value] ?? 0;
                        $isActive = $currentStatus === $s->value;
                    @endphp
                    <a
                        href="{{ route('admin.outlets.index', array_filter(['search' => $currentSearch, 'status' => $s->value])) }}"
                        class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium border {{ $isActive ? $c['on'] : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 shadow-sm' }}"
                    >
                        <span class="w-2 h-2 rounded-full {{ $c['dot'] }} {{ $isActive ? $c['dotRing'] : '' }}"></span>
                        {{ $s->label() }}
                        <span class="text-[11px] font-semibold rounded-full min-w-[20px] px-1.5 py-0.5 text-center leading-none {{ $isActive ? $c['badge'] : 'bg-gray-100 text-gray-400' }}">{{ $count }}</span>
                    </a>
                @endforeach

                @if ($currentSearch || $currentStatus)
                    <a
                        href="{{ route('admin.outlets.index') }}"
                        class="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-red-500 ml-1"
                    >
                        <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                        Clear filters
                    </a>
                @endif
            </div>

            {{-- View toggle --}}
            <div class="inline-flex rounded-lg bg-gray-100 p-0.5 ring-1 ring-gray-200/60 shrink-0">
                <button
                    @click="view = 'row'"
                    :class="view === 'row' ? 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200/60' : 'text-gray-400 hover:text-gray-600'"
                    class="relative inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-semibold transition-all duration-150 cursor-pointer"
                    title="List view"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                        <line x1="1" y1="3" x2="15" y2="3" /><line x1="1" y1="8" x2="15" y2="8" /><line x1="1" y1="13" x2="15" y2="13" />
                    </svg>
                    Rows
                </button>
                <button
                    @click="view = 'grid'"
                    :class="view === 'grid' ? 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200/60' : 'text-gray-400 hover:text-gray-600'"
                    class="relative inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-semibold transition-all duration-150 cursor-pointer"
                    title="Grid view"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="1" width="6" height="6" rx="1" /><rect x="9" y="1" width="6" height="6" rx="1" /><rect x="1" y="9" width="6" height="6" rx="1" /><rect x="9" y="9" width="6" height="6" rx="1" />
                    </svg>
                    Grid
                </button>
            </div>
        </div>

        @if ($outlets->isEmpty())
            {{-- Empty State --}}
            <x-card variant="subtle" class="text-center py-16">
                <x-heroicon-o-building-storefront class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                @if ($currentSearch || $currentStatus)
                    <p class="text-gray-400">No outlets match your filters</p>
                    <a href="{{ route('admin.outlets.index') }}" class="inline-flex items-center gap-1.5 mt-4 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                        Clear filters
                    </a>
                @else
                    <p class="text-gray-400 mb-6">No outlets yet</p>
                    <a href="{{ route('admin.outlets.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.2),0_1px_2px_rgba(0,0,0,0.15)] hover:bg-emerald-700 active:bg-emerald-800 active:shadow-[inset_0_1px_3px_rgba(0,0,0,0.2)] transition-all duration-100">
                        <x-heroicon-o-plus class="w-4 h-4 opacity-80" />
                        Create your first outlet
                    </a>
                @endif
            </x-card>
        @else
            {{-- ROW VIEW --}}
            <div x-show="view === 'row'" class="space-y-3">
                @foreach ($outlets as $outlet)
                    <x-card :href="route('admin.outlets.show', $outlet)" :interactive="true" class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3 min-w-0">
                                @if ($outlet->photo_url)
                                    <img src="{{ $outlet->photo_url }}" alt="{{ $outlet->name }}" class="w-10 h-10 rounded-xl object-cover shrink-0 mt-0.5 ring-1 ring-black/5">
                                @else
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0 mt-0.5 ring-1 ring-blue-200/60">
                                        <x-heroicon-s-building-storefront class="w-5 h-5 text-blue-600" />
                                    </div>
                                @endif
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
                                    {{ $outlet->contract_status->label() }}
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

            {{-- GRID VIEW --}}
            <div x-show="view === 'grid'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($outlets as $outlet)
                    @php
                        $statusColors = [
                            'active' => 'bg-emerald-100 text-emerald-700',
                            'inactive' => 'bg-gray-100 text-gray-600',
                            'pending' => 'bg-yellow-100 text-yellow-700',
                        ];
                    @endphp
                    <a
                        href="{{ route('admin.outlets.show', $outlet) }}"
                        class="group bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md hover:border-gray-300 transition-all duration-200"
                    >
                        {{-- Image --}}
                        <div class="relative h-40 overflow-hidden">
                            @if ($outlet->photo_url)
                                <img
                                    src="{{ $outlet->photo_url }}"
                                    alt="{{ $outlet->name }}"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50 flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-xl bg-white border-2 border-dashed border-gray-200 flex items-center justify-center shadow-sm">
                                        <x-heroicon-o-camera class="w-5 h-5 text-gray-300" />
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-2 font-medium">No photo</p>
                                </div>
                            @endif

                            {{-- Status badge overlay --}}
                            <div class="absolute top-3 left-3">
                                <span class="text-[11px] font-semibold rounded-full px-2 py-0.5 {{ $outlet->photo_url ? 'bg-white/80 text-gray-700 shadow-sm backdrop-blur-sm' : ($statusColors[$outlet->contract_status->value] ?? 'bg-gray-100 text-gray-600') }}">
                                    {{ $outlet->contract_status->label() }}
                                </span>
                            </div>

                            {{-- Bin count overlay --}}
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold rounded-full px-2 py-0.5 {{ $outlet->photo_url ? 'bg-black/40 text-white backdrop-blur-sm' : 'bg-gray-200 text-gray-500' }}">
                                    <x-heroicon-s-archive-box class="w-3 h-3" />
                                    {{ $outlet->current_bins_count }}
                                </span>
                            </div>
                        </div>

                        {{-- Card body --}}
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 truncate group-hover:text-emerald-600 transition-colors">{{ $outlet->name }}</h3>
                            <p class="text-xs text-gray-500 mt-1 truncate flex items-center gap-1">
                                <x-heroicon-o-map-pin class="w-3 h-3 shrink-0" />
                                {{ $outlet->address }}
                            </p>
                            @if ($outlet->contact_name)
                                <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
                                    <x-heroicon-o-user class="w-3 h-3 shrink-0" />
                                    {{ $outlet->contact_name }}
                                </p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $outlets->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>

<x-layouts.admin title="Bins">
    <x-slot:header>
        Bins
    </x-slot:header>

    {{-- Page header with action --}}
    <div class="flex items-center justify-between mb-4">
        <div></div>
        <a href="{{ route('admin.bins.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.2),0_1px_2px_rgba(0,0,0,0.15)] hover:bg-emerald-700 active:bg-emerald-800 active:shadow-[inset_0_1px_3px_rgba(0,0,0,0.2)] transition-all duration-100">
            <x-heroicon-o-archive-box class="w-4 h-4 opacity-80" />
            New
        </a>
    </div>

    {{-- Alpine wrapper — polls fleet status every 10s --}}
    <div
        x-data="{
            bins: @js($bins->getCollection()->keyBy('id')->map(fn ($b) => [
                'id' => $b->id,
                'fill_level' => $b->fill_level,
                'status' => $b->status->value,
                'is_online' => $b->last_seen_at && $b->last_seen_at->diffInSeconds(now()) < 60,
                'last_seen_human' => $b->last_seen_at?->diffForHumans(),
            ])),
            summary: @js($summary),
            init() {
                setInterval(() => this.refresh(), 10000);
            },
            async refresh() {
                try {
                    const res = await fetch('{{ route('admin.bins.fleet-status') }}');
                    if (!res.ok) return;
                    const data = await res.json();
                    this.bins = data.bins;
                    this.summary = data.summary;
                } catch(e) {}
            },
            bin(id) {
                return this.bins[id] || {};
            }
        }"
    >
        {{-- Fleet Summary --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                    <x-heroicon-s-archive-box class="w-5 h-5 text-emerald-600" />
                </div>
                <div>
                    <p class="text-xs text-gray-400">Total Bins</p>
                    <p class="text-xl font-bold text-gray-900" x-text="summary.total">{{ $summary['total'] }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                    <x-heroicon-s-signal class="w-5 h-5 text-blue-500" />
                </div>
                <div>
                    <p class="text-xs text-gray-400">Online</p>
                    <p class="text-xl font-bold text-gray-900" x-text="summary.online ?? 0">0</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                     x-bind:class="summary.needing_pickup > 0 ? 'bg-red-50' : 'bg-gray-50'">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"
                         x-bind:class="summary.needing_pickup > 0 ? 'text-red-500' : 'text-gray-400'">
                        <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Need Pickup</p>
                    <p class="text-xl font-bold" x-bind:class="summary.needing_pickup > 0 ? 'text-red-600' : 'text-gray-900'" x-text="summary.needing_pickup">{{ $summary['needing_pickup'] }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-violet-50 flex items-center justify-center shrink-0">
                    <x-heroicon-s-beaker class="w-5 h-5 text-violet-500" />
                </div>
                <div>
                    <p class="text-xs text-gray-400">Avg Fill</p>
                    <p class="text-xl font-bold text-gray-900"><span x-text="summary.avg_fill">{{ $summary['avg_fill'] }}</span>%</p>
                </div>
            </div>
        </div>

        {{-- Search & Filters --}}
        <form id="binFilterForm" method="GET" action="{{ route('admin.bins.index') }}" class="mb-6 space-y-3">
            {{-- Search bar --}}
            <div class="relative" x-data="{ focused: false }">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                </div>
                <input
                    id="binSearchInput"
                    name="search"
                    type="text"
                    placeholder="Search serial number or outlet..."
                    value="{{ request('search') }}"
                    autocomplete="off"
                    x-on:focus="focused = true"
                    x-on:blur="focused = false"
                    class="w-full rounded-xl border border-gray-200/80 bg-white/60 pl-11 pr-20 py-3 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition-all duration-150 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 focus:shadow-emerald-500/5"
                >
                <div class="absolute inset-y-0 right-3 flex items-center gap-2">
                    {{-- Keyboard shortcut hint --}}
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
                    {{-- Search submit button --}}
                    <button type="submit" class="rounded-lg bg-emerald-600 p-1.5 text-white shadow-sm hover:bg-emerald-700 active:bg-emerald-800 transition-colors cursor-pointer" title="Search">
                        <x-heroicon-s-arrow-right class="w-4 h-4" />
                    </button>
                </div>
            </div>

            {{-- Filter controls --}}
            <div class="flex flex-wrap items-center gap-3">
                <div class="min-w-[140px]">
                    <x-dropdown.select
                        name="status"
                        placeholder="All Status"
                        :options="collect($statuses)->mapWithKeys(fn($s) => [$s->value => $s->label()])->toArray()"
                        :selected="request('status')"
                    />
                </div>
                <div class="min-w-[160px]">
                    <x-dropdown.select
                        name="outlet"
                        placeholder="All Outlets"
                        :options="$outlets->pluck('name', 'id')->toArray()"
                        :selected="request('outlet')"
                        :searchable="$outlets->count() > 5"
                    />
                </div>
                <div class="min-w-[160px]">
                    <x-dropdown.select
                        name="sort"
                        placeholder="Sort by"
                        :options="[
                            'fill_desc' => 'Fill: High to Low',
                            'fill_asc' => 'Fill: Low to High',
                            'serial' => 'Serial Number',
                            'recent' => 'Recently Updated',
                        ]"
                        :selected="request('sort', 'fill_desc')"
                    />
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer whitespace-nowrap py-2.5">
                    <input
                        type="checkbox"
                        name="ready_for_pickup"
                        value="1"
                        @checked(request('ready_for_pickup'))
                        class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-200 cursor-pointer"
                        onchange="this.form.submit()"
                    >
                    Ready for pickup
                </label>
                @if (request()->hasAny(['search', 'status', 'outlet', 'sort', 'ready_for_pickup']))
                    <a href="{{ route('admin.bins.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-red-500 transition-colors ml-1">
                        <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                        Clear filters
                    </a>
                @endif
            </div>
        </form>

        @once
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var searchInput = document.getElementById('binSearchInput');
            if (!searchInput) return;

            document.addEventListener('keydown', function (e) {
                // "/" to focus search — only when not already typing in an input/textarea
                if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName) && !document.activeElement.isContentEditable) {
                    e.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                }
                // Cmd+K / Ctrl+K to focus search
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                }
                // Escape to blur search
                if (e.key === 'Escape' && document.activeElement === searchInput) {
                    searchInput.blur();
                }
            });

            // Auto-submit on dropdown change
            var filterForm = document.getElementById('binFilterForm');
            if (filterForm) {
                filterForm.addEventListener('change', function (e) {
                    if (e.target.matches('[data-dropdown-value]')) {
                        this.submit();
                    }
                });
            }
        });
        </script>
        @endonce

        @if ($bins->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm text-center py-16">
                <x-heroicon-o-archive-box class="w-12 h-12 text-gray-200 mx-auto mb-3" />
                @if (request()->hasAny(['search', 'status', 'outlet', 'ready_for_pickup']))
                    <p class="text-gray-400">No bins match your filters</p>
                    <a href="{{ route('admin.bins.index') }}" class="inline-flex items-center gap-1.5 mt-4 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                        Clear filters
                    </a>
                @else
                    <p class="text-gray-400 mb-6">No bins yet</p>
                    <a href="{{ route('admin.bins.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.2),0_1px_2px_rgba(0,0,0,0.15)] hover:bg-emerald-700 active:bg-emerald-800 active:shadow-[inset_0_1px_3px_rgba(0,0,0,0.2)] transition-all duration-100">
                        <x-heroicon-o-plus class="w-4 h-4 opacity-80" />
                        Create your first bin
                    </a>
                @endif
            </div>
        @else
            {{-- Table-style header (desktop) — widths must mirror row columns --}}
            <div class="hidden lg:flex items-center gap-4 px-4 pb-2 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                <div class="w-12 shrink-0 text-center">Fill</div>
                <div class="flex-1 min-w-0">Bin</div>
                <div class="w-24 shrink-0 text-center">Status</div>
                <div class="w-24 shrink-0 text-right">Detections</div>
                <div class="w-4 shrink-0"></div>{{-- chevron spacer --}}
            </div>

            <div class="space-y-1.5">
                @foreach ($bins as $bin)
                    @php
                        $binId = $bin->id;
                    @endphp
                    <a
                        href="{{ route('admin.bins.show', $bin) }}"
                        class="group block bg-white rounded-xl border border-gray-200/80 p-4 hover:shadow-md hover:border-emerald-200 transition-all cursor-pointer"
                        :class="{ 'ring-2 ring-red-200 border-red-200': bin({{ $binId }}).fill_level >= 80 }"
                    >
                        <div class="flex items-center gap-4">
                            {{-- SVG Gauge --}}
                            <div class="shrink-0 w-12 h-12 relative flex items-center justify-center">
                                <svg width="48" height="48" viewBox="0 0 48 48" class="transform rotate-[135deg]">
                                    <circle cx="24" cy="24" r="19" fill="none" stroke="#f3f4f6" stroke-width="4"
                                        stroke-dasharray="{{ 2 * M_PI * 19 * 0.75 }} {{ 2 * M_PI * 19 }}" stroke-linecap="round" />
                                    <circle cx="24" cy="24" r="19" fill="none"
                                        stroke-width="4"
                                        stroke-dasharray="{{ 2 * M_PI * 19 * 0.75 }} {{ 2 * M_PI * 19 }}"
                                        :stroke-dashoffset="{{ 2 * M_PI * 19 * 0.75 }} - ({{ 2 * M_PI * 19 * 0.75 }} * bin({{ $binId }}).fill_level / 100)"
                                        :stroke="bin({{ $binId }}).fill_level >= 80 ? '#ef4444' : (bin({{ $binId }}).fill_level >= 50 ? '#eab308' : '#10b981')"
                                        stroke-linecap="round"
                                        class="transition-all duration-700" />
                                </svg>
                                <span class="absolute text-[10px] font-bold"
                                    :style="'color:' + (bin({{ $binId }}).fill_level >= 80 ? '#ef4444' : (bin({{ $binId }}).fill_level >= 50 ? '#eab308' : '#10b981'))"
                                    x-text="bin({{ $binId }}).fill_level + '%'">{{ $bin->fill_level }}%</span>
                            </div>

                            {{-- Identity --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="font-semibold text-gray-900 text-sm group-hover:text-emerald-700 transition-colors">{{ $bin->serial_number }}</h2>
                                    {{-- Online dot --}}
                                    <template x-if="bin({{ $binId }}).is_online">
                                        <span class="relative flex h-2 w-2" title="Online">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                        </span>
                                    </template>
                                    <template x-if="!bin({{ $binId }}).is_online && bin({{ $binId }}).last_seen_human">
                                        <span class="w-2 h-2 rounded-full bg-amber-400" :title="'Last seen ' + bin({{ $binId }}).last_seen_human"></span>
                                    </template>
                                    <template x-if="!bin({{ $binId }}).is_online && !bin({{ $binId }}).last_seen_human">
                                        <span class="w-2 h-2 rounded-full bg-gray-300" title="Never connected"></span>
                                    </template>
                                    {{-- Pickup badge --}}
                                    <template x-if="bin({{ $binId }}).fill_level >= 80">
                                        <span class="text-[10px] font-semibold bg-red-100 text-red-700 rounded-full px-2 py-0.5 animate-pulse">PICKUP</span>
                                    </template>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5 truncate">
                                    @if ($bin->currentAssignment)
                                        {{ $bin->currentAssignment->outlet->name }}
                                    @else
                                        <span class="text-gray-400 italic">Unassigned</span>
                                    @endif
                                </p>
                            </div>

                            {{-- Status badge --}}
                            <div class="shrink-0 w-24 flex justify-center">
                                <span class="text-[10px] font-semibold rounded-full px-2.5 py-1"
                                    :class="{
                                        'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200': bin({{ $binId }}).status === 'active',
                                        'bg-gray-50 text-gray-500 ring-1 ring-gray-200': bin({{ $binId }}).status === 'inactive',
                                        'bg-orange-50 text-orange-700 ring-1 ring-orange-200': bin({{ $binId }}).status === 'maintenance'
                                    }"
                                    x-text="bin({{ $binId }}).status.charAt(0).toUpperCase() + bin({{ $binId }}).status.slice(1)">{{ $bin->status->label() }}</span>
                            </div>

                            {{-- Detection count --}}
                            <div class="shrink-0 w-24 text-right">
                                <div class="flex items-center justify-end gap-1.5 text-xs text-gray-500">
                                    <x-heroicon-o-eye class="w-3.5 h-3.5 text-gray-400" />
                                    <span class="font-medium">{{ $bin->detection_events_count }}</span>
                                </div>
                                @if ($bin->detection_events_max_detected_at)
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($bin->detection_events_max_detected_at)->diffForHumans(short: true) }}</p>
                                @endif
                            </div>

                            {{-- Chevron --}}
                            <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-300 shrink-0 group-hover:text-emerald-500 transition-colors" />
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $bins->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>

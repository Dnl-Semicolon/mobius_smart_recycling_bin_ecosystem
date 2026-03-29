<x-layouts.admin :title="$outlet->name">
    <x-slot:back>
        <x-back-button href="{{ route('admin.outlets.index') }}" />
    </x-slot:back>

    <x-slot:header>
        {{ $outlet->name }}
    </x-slot:header>

    <x-slot:actions>
        <x-button href="{{ route('admin.outlets.edit', $outlet) }}">
            <x-heroicon-o-pencil-square class="w-4 h-4" />
            Edit
        </x-button>
    </x-slot:actions>

    @php
        $statusColors = [
            'active' => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200/60',
            'inactive' => 'bg-gray-100 text-gray-600 ring-1 ring-gray-200/60',
            'pending' => 'bg-amber-100 text-amber-700 ring-1 ring-amber-200/60',
        ];
        $structuredHours = $outlet->getStructuredHours();
        $dayLabels = [
            'monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed',
            'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun',
        ];
    @endphp

    <div class="space-y-5" x-data="{ lightbox: false }">
        {{-- ============================================================ --}}
        {{-- HERO — Photo / Empty State + Identity                        --}}
        {{-- ============================================================ --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            {{-- Photo hero / empty state --}}
            <div
                class="relative h-56 sm:h-64 {{ $outlet->photo_url ? 'cursor-pointer group' : '' }}"
                @if ($outlet->photo_url) @click="lightbox = true" @endif
            >
                @if ($outlet->photo_url)
                    <img
                        src="{{ $outlet->photo_url }}"
                        alt="{{ $outlet->name }}"
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.02]"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
                    {{-- Expand hint --}}
                    <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-black/40 backdrop-blur-sm px-3 py-1.5 text-xs font-medium text-white/90">
                            <x-heroicon-o-arrows-pointing-out class="w-3.5 h-3.5" />
                            View full
                        </span>
                    </div>
                @else
                    <div class="w-full h-full bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50 flex flex-col items-center justify-center">
                        <div class="w-16 h-16 rounded-2xl bg-white border-2 border-dashed border-gray-200 flex items-center justify-center mb-3 shadow-sm">
                            <x-heroicon-o-camera class="w-7 h-7 text-gray-300" />
                        </div>
                        <p class="text-sm font-medium text-gray-400">No photo uploaded</p>
                        <a
                            href="{{ route('admin.outlets.edit', $outlet) }}"
                            class="mt-2 text-xs text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center gap-1 transition-colors"
                        >
                            <x-heroicon-o-plus class="w-3 h-3" />
                            Add one
                        </a>
                    </div>
                @endif
            </div>

            {{-- Identity bar --}}
            <div class="p-5 lg:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="text-xs font-semibold rounded-full px-2.5 py-1 {{ $statusColors[$outlet->contract_status->value] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $outlet->contract_status->label() }}
                            </span>
                            @if ($outlet->brand)
                                <span class="text-xs font-medium rounded-full px-2.5 py-1 bg-blue-50 text-blue-600 ring-1 ring-blue-200/60 flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full" style="background: {{ $outlet->brand->primary_color }}"></span>
                                    {{ $outlet->brand->name }}
                                </span>
                            @endif
                        </div>
                        <h2 class="text-lg font-bold text-gray-900 truncate">{{ $outlet->name }}</h2>
                        <p class="text-sm text-gray-500 mt-0.5 flex items-center gap-1.5">
                            <x-heroicon-o-map-pin class="w-3.5 h-3.5 shrink-0" />
                            <span class="truncate">{{ $outlet->address }}</span>
                        </p>
                    </div>

                    {{-- Quick stats --}}
                    <div class="flex items-center gap-4 shrink-0">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900">{{ $outlet->current_bins_count }}</p>
                            <p class="text-[11px] text-gray-400 font-medium uppercase tracking-wider">{{ Str::plural('Bin', $outlet->current_bins_count) }}</p>
                        </div>
                        @if ($outlet->latitude && $outlet->longitude)
                            <a
                                href="https://www.google.com/maps?q={{ $outlet->latitude }},{{ $outlet->longitude }}"
                                target="_blank"
                                class="w-10 h-10 rounded-xl bg-emerald-50 hover:bg-emerald-100 flex items-center justify-center transition-colors ring-1 ring-emerald-200/60"
                                title="Open in Google Maps"
                            >
                                <x-heroicon-s-map-pin class="w-5 h-5 text-emerald-600" />
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- DETAIL GRID — 2-column on desktop                            --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- LEFT COLUMN (2/3) --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Operating Hours --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 lg:p-6">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center ring-1 ring-orange-200/60">
                            <x-heroicon-s-clock class="w-4 h-4 text-orange-600" />
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900">Operating Hours</h3>
                    </div>

                    @if ($structuredHours)
                        <div class="grid grid-cols-7 gap-1.5">
                            @foreach ($dayLabels as $key => $label)
                                @php
                                    $day = $structuredHours[$key] ?? null;
                                    $isOpen = $day && ($day['open'] ?? false);
                                @endphp
                                <div class="rounded-xl p-3 text-center {{ $isOpen ? 'bg-emerald-50/60 ring-1 ring-emerald-100' : 'bg-gray-50 ring-1 ring-gray-100' }}">
                                    <p class="text-[11px] font-bold uppercase tracking-wider {{ $isOpen ? 'text-emerald-600' : 'text-gray-400' }}">{{ $label }}</p>
                                    @if ($isOpen)
                                        <p class="text-xs font-semibold text-gray-900 mt-1.5">{{ $day['from'] }}</p>
                                        <div class="w-2.5 h-px bg-gray-300 mx-auto my-0.5"></div>
                                        <p class="text-xs font-semibold text-gray-900">{{ $day['to'] }}</p>
                                    @else
                                        <p class="text-[11px] text-gray-400 italic mt-2">Closed</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @elseif ($outlet->operating_hours)
                        <p class="text-sm text-gray-700 flex items-center gap-1.5">
                            <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                            {{ $outlet->operating_hours }}
                        </p>
                    @else
                        <p class="text-sm text-gray-400">No hours set</p>
                    @endif
                </div>

                {{-- Assigned Bins --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 lg:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center ring-1 ring-blue-200/60">
                                <x-heroicon-s-archive-box class="w-4 h-4 text-blue-600" />
                            </div>
                            <h3 class="text-sm font-semibold text-gray-900">Assigned Bins</h3>
                        </div>
                        <span class="text-xs font-semibold text-gray-400 bg-gray-100 rounded-full px-2 py-0.5">{{ $outlet->bins->count() }}</span>
                    </div>

                    @if ($outlet->bins->isEmpty())
                        <div class="rounded-xl bg-gray-50 border border-gray-100 py-10 text-center">
                            <x-heroicon-o-archive-box class="w-8 h-8 text-gray-300 mx-auto mb-2" />
                            <p class="text-sm text-gray-400 font-medium">No bins assigned</p>
                            <p class="text-xs text-gray-400 mt-0.5">Assign bins from the bin management page</p>
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach ($outlet->bins as $bin)
                                @php
                                    $fillColor = match(true) {
                                        $bin->fill_level >= 80 => ['bar' => 'bg-red-500', 'text' => 'text-red-600', 'bg' => 'bg-red-50'],
                                        $bin->fill_level >= 50 => ['bar' => 'bg-amber-500', 'text' => 'text-amber-600', 'bg' => 'bg-amber-50'],
                                        default => ['bar' => 'bg-emerald-500', 'text' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
                                    };
                                    $binStatusColors = [
                                        'active' => 'text-emerald-600',
                                        'inactive' => 'text-gray-500',
                                        'maintenance' => 'text-orange-600',
                                    ];
                                @endphp
                                <a
                                    href="{{ route('admin.bins.show', $bin) }}"
                                    class="flex items-center gap-4 rounded-xl px-4 py-3.5 hover:bg-gray-50 transition-colors duration-100 group"
                                >
                                    <div class="w-10 h-10 rounded-xl {{ $fillColor['bg'] }} flex items-center justify-center shrink-0 ring-1 ring-black/5">
                                        <x-heroicon-s-archive-box class="w-5 h-5 {{ $fillColor['text'] }}" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 group-hover:text-emerald-600 transition-colors">{{ $bin->serial_number }}</p>
                                        <p class="text-xs {{ $binStatusColors[$bin->status->value] ?? 'text-gray-500' }}">{{ $bin->status->label() }}</p>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <div class="w-20 h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full {{ $fillColor['bar'] }} rounded-full transition-all duration-300" style="width: {{ $bin->fill_level }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold tabular-nums w-10 text-right {{ $fillColor['text'] }}">{{ $bin->fill_level }}%</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- RIGHT COLUMN (1/3) --}}
            <div class="space-y-5">

                {{-- Location --}}
                @if ($outlet->latitude && $outlet->longitude)
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="h-40 bg-gray-100" id="outlet-mini-map"></div>
                        <div class="p-4">
                            <div class="flex items-center gap-2.5 mb-3">
                                <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center ring-1 ring-emerald-200/60">
                                    <x-heroicon-s-map-pin class="w-3.5 h-3.5 text-emerald-600" />
                                </div>
                                <h3 class="text-sm font-semibold text-gray-900">Location</h3>
                            </div>
                            <p class="text-xs text-gray-500 font-mono tabular-nums">{{ $outlet->latitude }}, {{ $outlet->longitude }}</p>
                        </div>
                    </div>
                @endif

                {{-- Contact Info --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-violet-100 flex items-center justify-center ring-1 ring-violet-200/60">
                            <x-heroicon-s-user class="w-3.5 h-3.5 text-violet-600" />
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900">Contact</h3>
                    </div>

                    @if ($outlet->contact_name || $outlet->contact_phone || $outlet->contact_email)
                        <div class="space-y-3">
                            @if ($outlet->contact_name)
                                <div>
                                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Name</p>
                                    <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $outlet->contact_name }}</p>
                                </div>
                            @endif
                            @if ($outlet->contact_phone)
                                <div>
                                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Phone</p>
                                    <a href="tel:{{ $outlet->contact_phone }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600 transition-colors mt-0.5 inline-flex items-center gap-1.5">
                                        <x-heroicon-o-phone class="w-3.5 h-3.5 text-gray-400" />
                                        {{ $outlet->contact_phone }}
                                    </a>
                                </div>
                            @endif
                            @if ($outlet->contact_email)
                                <div>
                                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Email</p>
                                    <a href="mailto:{{ $outlet->contact_email }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 transition-colors mt-0.5 inline-flex items-center gap-1.5 truncate">
                                        <x-heroicon-o-envelope class="w-3.5 h-3.5 shrink-0" />
                                        <span class="truncate">{{ $outlet->contact_email }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4">
                            <x-heroicon-o-user class="w-6 h-6 text-gray-300 mx-auto mb-1.5" />
                            <p class="text-xs text-gray-400">No contact info</p>
                        </div>
                    @endif
                </div>

                {{-- Notes --}}
                @if ($outlet->notes)
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                        <div class="flex items-center gap-2.5 mb-3">
                            <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center ring-1 ring-amber-200/60">
                                <x-heroicon-s-document-text class="w-3.5 h-3.5 text-amber-600" />
                            </div>
                            <h3 class="text-sm font-semibold text-gray-900">Notes</h3>
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-wrap">{{ $outlet->notes }}</p>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center ring-1 ring-gray-200/60">
                            <x-heroicon-s-cog-6-tooth class="w-3.5 h-3.5 text-gray-500" />
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900">Actions</h3>
                    </div>
                    <div class="space-y-1">
                        <a
                            href="{{ route('admin.outlets.edit', $outlet) }}"
                            class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors"
                        >
                            <x-heroicon-o-pencil-square class="w-4 h-4 text-gray-400" />
                            Edit outlet details
                        </a>
                        <form action="{{ route('admin.outlets.destroy', $outlet) }}" method="POST" onsubmit="return confirm('Delete this outlet? This action can be undone (soft delete).')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors w-full cursor-pointer">
                                <x-heroicon-o-trash class="w-4 h-4" />
                                Delete outlet
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- LIGHTBOX — Full-screen image viewer                          --}}
        {{-- ============================================================ --}}
        @if ($outlet->photo_url)
            <div
                x-show="lightbox"
                x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click.self="lightbox = false"
                @keydown.escape.window="lightbox = false"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 backdrop-blur-sm p-4 sm:p-8 cursor-zoom-out"
                style="margin: 0 !important;"
            >
                {{-- Close button --}}
                <button
                    @click="lightbox = false"
                    class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-sm flex items-center justify-center transition-colors cursor-pointer"
                >
                    <x-heroicon-o-x-mark class="w-5 h-5 text-white" />
                </button>

                {{-- Image --}}
                <img
                    x-show="lightbox"
                    x-transition:enter="transition ease-out duration-300 delay-75"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    src="{{ $outlet->photo_url }}"
                    alt="{{ $outlet->name }}"
                    class="max-w-full max-h-full object-contain rounded-lg shadow-2xl cursor-default"
                    @click.stop
                >

                {{-- Caption --}}
                <div
                    x-show="lightbox"
                    x-transition:enter="transition ease-out duration-300 delay-150"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="absolute bottom-6 left-1/2 -translate-x-1/2"
                >
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 backdrop-blur-sm px-4 py-2 text-sm font-medium text-white/80">
                        <x-heroicon-o-building-storefront class="w-4 h-4" />
                        {{ $outlet->name }}
                    </span>
                </div>
            </div>
        @endif
    </div>

    {{-- Mini-map initialization --}}
    @if ($outlet->latitude && $outlet->longitude)
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const L = window.L;
            const container = document.getElementById('outlet-mini-map');
            if (!L || !container) return;

            const map = L.map(container, {
                scrollWheelZoom: false,
                zoomControl: false,
                dragging: false,
                attributionControl: false,
            }).setView([{{ $outlet->latitude }}, {{ $outlet->longitude }}], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
            L.marker([{{ $outlet->latitude }}, {{ $outlet->longitude }}]).addTo(map);
        });
        </script>
    @endif
</x-layouts.admin>

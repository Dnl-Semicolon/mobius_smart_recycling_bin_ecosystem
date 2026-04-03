<x-layouts.admin :title="$bin->serial_number">
    <x-slot:back>
        <x-back-button href="{{ route('admin.bins.index') }}" />
    </x-slot:back>

    <x-slot:header>
        {{ $bin->serial_number }}
    </x-slot:header>

    <x-slot:actions>
        <x-button href="{{ route('admin.bins.edit', $bin) }}">
            <x-heroicon-o-pencil-square class="w-4 h-4" />
            Edit
        </x-button>
    </x-slot:actions>

    @php
        $isReadyForPickup = $bin->status === \App\Enums\BinStatus::Active && $bin->fill_level >= 80;
        $statusColors = [
            'active' => 'bg-emerald-100 text-emerald-700',
            'inactive' => 'bg-gray-100 text-gray-600',
            'maintenance' => 'bg-orange-100 text-orange-700',
        ];
    @endphp

    <div class="space-y-5">
        {{-- ============================================================ --}}
        {{-- HERO — Identity + Status Badges + Tab Nav                     --}}
        {{-- ============================================================ --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 lg:p-6">
                <div class="flex flex-col lg:flex-row lg:items-start gap-5 lg:gap-8">
                    {{-- Radial Gauge --}}
                    <div class="flex justify-center lg:justify-start shrink-0">
                        <x-bin-gauge :value="$bin->fill_level" :size="140" />
                    </div>

                    {{-- Identity + Badges --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <span class="text-sm font-semibold rounded-full px-3 py-0.5 {{ $statusColors[$bin->status->value] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $bin->status->label() }}
                            </span>
                            @if ($isReadyForPickup)
                                <span class="text-sm font-semibold bg-red-100 text-red-700 rounded-full px-3 py-0.5 flex items-center gap-1 animate-pulse">
                                    <x-heroicon-s-exclamation-triangle class="w-3.5 h-3.5" />
                                    Needs Pickup
                                </span>
                            @endif
                            @if ($bin->activePickupRequest)
                                @if ($bin->activePickupRequest->status === 'pending')
                                    <span class="text-sm font-medium bg-amber-100 text-amber-700 rounded-full px-3 py-0.5 flex items-center gap-1">
                                        <x-heroicon-s-clock class="w-3.5 h-3.5" />
                                        Pickup Pending
                                    </span>
                                @else
                                    <span class="text-sm font-medium bg-blue-100 text-blue-700 rounded-full px-3 py-0.5 flex items-center gap-1">
                                        <x-heroicon-s-truck class="w-3.5 h-3.5" />
                                        Claimed by {{ $bin->activePickupRequest->assignedTo->name }}
                                    </span>
                                @endif
                            @endif
                        </div>

                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-2 text-sm">
                            <div>
                                <span class="text-gray-400 text-xs">Outlet</span>
                                @if ($bin->currentAssignment)
                                    <a href="{{ route('admin.outlets.show', $bin->currentAssignment->outlet) }}" class="block font-semibold text-emerald-600 hover:text-emerald-700 truncate">
                                        {{ $bin->currentAssignment->outlet->name }}
                                    </a>
                                @else
                                    <p class="font-medium text-gray-400">Unassigned</p>
                                @endif
                            </div>
                            <div>
                                <span class="text-gray-400 text-xs">Detections</span>
                                <p class="font-semibold text-gray-900">{{ number_format($quickStats['total_detections']) }}</p>
                            </div>
                            <div>
                                <span class="text-gray-400 text-xs">Pickups</span>
                                <p class="font-semibold text-gray-900">{{ $quickStats['completed_pickups'] }} completed</p>
                            </div>
                            <div>
                                <span class="text-gray-400 text-xs">Assigned</span>
                                <p class="font-semibold text-gray-900">
                                    @if ($quickStats['days_assigned'] !== null)
                                        {{ $quickStats['days_assigned'] }} {{ Str::plural('day', $quickStats['days_assigned']) }}
                                    @else
                                        —
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-5 lg:px-6 pb-3 border-t border-gray-100">
                <x-bin-tab-nav :bin="$bin" active="overview" />
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- LIVE TELEMETRY — Alpine.js auto-polling every 10s             --}}
        {{-- ============================================================ --}}
        <div
            x-data="{
                t: @js($telemetry),
                fillLevel: {{ $bin->fill_level }},
                polling: null,
                async refresh() {
                    try {
                        const res = await fetch('{{ route('admin.bins.telemetry', $bin) }}');
                        if (!res.ok) return;
                        const data = await res.json();
                        this.t = { ...this.t, ...data };
                        this.fillLevel = data.fill_level;
                    } catch(e) {}
                },
                init() {
                    this.polling = setInterval(() => this.refresh(), 10000);
                },
                destroy() {
                    clearInterval(this.polling);
                },
                formatWeight(g) {
                    return g >= 1000 ? (g / 1000).toFixed(1) + 'kg' : g + 'g';
                },
                barColor(pct) {
                    if (pct >= 80) return 'bg-red-500';
                    if (pct >= 60) return 'bg-amber-400';
                    return 'bg-emerald-500';
                }
            }"
            class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <x-heroicon-o-signal class="w-4 h-4 text-gray-400" />
                    Live Telemetry
                </h3>
                <div class="flex items-center gap-2">
                    <template x-if="t.is_online">
                        <div class="flex items-center gap-1.5">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-medium text-emerald-600">Online</span>
                        </div>
                    </template>
                    <template x-if="!t.is_online && t.last_seen_at">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <span class="text-xs text-gray-500">Last seen <span x-text="t.last_seen_human"></span></span>
                        </div>
                    </template>
                    <template x-if="!t.is_online && !t.last_seen_at">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span>
                            <span class="text-xs text-gray-400">Never connected</span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="p-5">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    {{-- Gauges column (8/12) --}}
                    <div class="lg:col-span-8 space-y-4">
                        {{-- Solids --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Solids</span>
                                </div>
                                <span class="text-sm font-mono font-bold text-gray-800" x-text="t.solids_fill + '%'"></span>
                            </div>
                            <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-700 ease-out"
                                    :class="barColor(t.solids_fill)"
                                    :style="'width:' + t.solids_fill + '%'"
                                ></div>
                            </div>
                            <div class="flex justify-between mt-1">
                                <span class="text-[11px] text-gray-400 font-mono">
                                    <span x-text="Number(t.solids_volume_ml).toLocaleString()"></span>mL
                                    / <span x-text="Number(t.solids_max_ml).toLocaleString()"></span>mL
                                </span>
                                <span class="text-[11px] text-gray-400 font-mono" x-text="t.solids_weight_g + 'g'"></span>
                            </div>
                        </div>

                        {{-- Liquid --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                                    <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Liquid</span>
                                </div>
                                <span class="text-sm font-mono font-bold text-gray-800" x-text="t.liquid_fill + '%'"></span>
                            </div>
                            <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-700 ease-out bg-cyan-500"
                                    :class="{ 'bg-red-500': t.liquid_fill >= 80, 'bg-amber-400': t.liquid_fill >= 60 && t.liquid_fill < 80, 'bg-cyan-500': t.liquid_fill < 60 }"
                                    :style="'width:' + t.liquid_fill + '%'"
                                ></div>
                            </div>
                            <div class="flex justify-between mt-1">
                                <span class="text-[11px] text-gray-400 font-mono">
                                    <span x-text="Number(t.liquid_volume_ml).toLocaleString()"></span>mL
                                    / <span x-text="Number(t.liquid_max_ml).toLocaleString()"></span>mL
                                </span>
                                <span class="text-[11px] text-gray-400 font-mono" x-text="t.liquid_weight_g + 'g'"></span>
                            </div>
                        </div>

                        {{-- Overall --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                                    <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Overall</span>
                                </div>
                                <span class="text-sm font-mono font-bold text-gray-800" x-text="fillLevel + '%'"></span>
                            </div>
                            <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-700 ease-out"
                                    :class="barColor(fillLevel)"
                                    :style="'width:' + fillLevel + '%'"
                                ></div>
                            </div>
                        </div>
                    </div>

                    {{-- Stats column (4/12) --}}
                    <div class="lg:col-span-4 grid grid-cols-2 lg:grid-cols-1 gap-3">
                        <div class="bg-gray-50 rounded-xl p-4 flex flex-col items-center justify-center">
                            <p class="text-2xl font-black text-gray-900 font-mono tracking-tight" x-text="formatWeight(t.total_weight_g)"></p>
                            <p class="text-[10px] text-gray-400 uppercase tracking-widest mt-1">Weight</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 flex flex-col items-center justify-center">
                            <p class="text-2xl font-black text-gray-900 font-mono tracking-tight">
                                <span x-text="t.fill_height_cm"></span><span class="text-base font-bold text-gray-400">cm</span>
                            </p>
                            <p class="text-[10px] text-gray-400 uppercase tracking-widest mt-1">Fill Height</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 flex flex-col items-center justify-center lg:col-span-1 col-span-2">
                            <p class="text-2xl font-black text-gray-900 font-mono tracking-tight" x-text="t.today_detections"></p>
                            <p class="text-[10px] text-gray-400 uppercase tracking-widest mt-1">Today</p>
                        </div>
                        <template x-if="t.ip_address">
                            <div class="col-span-2 lg:col-span-1 text-center py-1">
                                <p class="text-[11px] font-mono text-gray-400" x-text="t.ip_address"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- STAT PILLS — compact row of key numbers                       --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3.5 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center shrink-0">
                    <x-heroicon-s-eye class="w-4 h-4 text-violet-500" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 leading-none">Today</p>
                    <p class="text-base font-bold text-gray-900 mt-0.5">{{ $quickStats['today_detections'] }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3.5 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                    <x-heroicon-s-calendar class="w-4 h-4 text-blue-500" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 leading-none">This Week</p>
                    <p class="text-base font-bold text-gray-900 mt-0.5">{{ $quickStats['week_detections'] }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3.5 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                    <x-heroicon-s-scale class="w-4 h-4 text-amber-500" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 leading-none">Total Weight</p>
                    <p class="text-base font-bold text-gray-900 mt-0.5">
                        {{ $telemetry['total_weight_detections'] >= 1000 ? number_format($telemetry['total_weight_detections'] / 1000, 1) . 'kg' : $telemetry['total_weight_detections'] . 'g' }}
                    </p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3.5 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                    <x-heroicon-s-signal class="w-4 h-4 text-emerald-500" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 leading-none">Confidence</p>
                    <p class="text-base font-bold text-gray-900 mt-0.5">{{ $quickStats['avg_confidence'] ?: '—' }}%</p>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- MAIN GRID — 2/3 content + 1/3 sidebar                        --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="lg:col-span-2 space-y-5">
                {{-- Waste Distribution + Recent Activity --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Waste Type Chart --}}
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                        <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-1.5">
                            <x-heroicon-o-chart-pie class="w-4 h-4 text-gray-400" />
                            Waste Breakdown
                        </h3>
                        @if (empty($wasteTypeChart))
                            <div class="flex flex-col items-center justify-center py-10">
                                <x-heroicon-o-beaker class="w-10 h-10 text-gray-200 mb-2" />
                                <p class="text-sm text-gray-400">No detections yet</p>
                            </div>
                        @else
                            <div class="flex justify-center">
                                <canvas id="binWasteChart" width="200" height="200"></canvas>
                            </div>
                        @endif
                    </div>

                    {{-- Recent Activity Feed --}}
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                        <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-1.5">
                            <x-heroicon-o-bolt class="w-4 h-4 text-gray-400" />
                            Recent Activity
                        </h3>
                        @if ($recentActivity->isEmpty())
                            <div class="flex flex-col items-center justify-center py-10">
                                <x-heroicon-o-clock class="w-10 h-10 text-gray-200 mb-2" />
                                <p class="text-sm text-gray-400">No activity yet</p>
                            </div>
                        @else
                            <div class="divide-y divide-gray-50">
                                @foreach ($recentActivity as $detection)
                                    <a href="{{ route('admin.detection-events.show', $detection) }}" class="flex items-center gap-2.5 py-2 first:pt-0 last:pb-0 group">
                                        <div class="w-7 h-7 rounded-md {{ $detection->waste_type ? $detection->waste_type->iconColor() . ' ' . $detection->waste_type->bgColor() : 'text-gray-400 bg-gray-50' }} flex items-center justify-center shrink-0">
                                            <x-heroicon-s-beaker class="w-3.5 h-3.5" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-800 truncate group-hover:text-emerald-600 transition-colors">
                                                {{ $detection->waste_type?->label() ?? 'Pending' }}
                                            </p>
                                            <p class="text-[11px] text-gray-400">{{ $detection->detected_at->diffForHumans() }}</p>
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            @if ($detection->confidence)
                                                <span class="text-[11px] font-mono {{ $detection->confidence >= 90 ? 'text-emerald-600 bg-emerald-50' : ($detection->confidence >= 70 ? 'text-amber-600 bg-amber-50' : 'text-gray-500 bg-gray-50') }} px-1.5 py-0.5 rounded">
                                                    {{ $detection->confidence }}%
                                                </span>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            <a href="{{ route('admin.bins.detections', $bin) }}" class="mt-4 text-sm text-emerald-600 hover:text-emerald-700 inline-flex items-center gap-1 font-medium">
                                View all detections
                                <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
                            </a>
                        @endif
                    </div>
                </div>

                {{-- AI Inference Status --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-1.5">
                        <x-heroicon-o-cpu-chip class="w-4 h-4 text-gray-400" />
                        AI Pipeline
                    </h3>
                    <div class="flex flex-wrap items-center gap-4">
                        @php
                            $hasPending = $recentActivity->contains(fn ($d) => $d->waste_type === null);
                            $hasClassified = $recentActivity->contains(fn ($d) => $d->waste_type !== null);
                        @endphp
                        <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2">
                            @if ($hasClassified)
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                <span class="text-sm text-gray-700 font-medium">Model Active</span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                <span class="text-sm text-gray-500">Awaiting Data</span>
                            @endif
                        </div>
                        @if ($hasPending)
                            <div class="flex items-center gap-2 bg-amber-50 rounded-lg px-3 py-2">
                                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                                <span class="text-sm text-amber-700">Pending inference</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-1.5 ml-auto text-gray-400">
                            <x-heroicon-o-camera class="w-4 h-4" />
                            <span class="text-xs">Live feed requires hardware</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column — Assignment & Actions --}}
            <div class="space-y-5">
                {{-- Current Assignment --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-1.5">
                        <x-heroicon-o-map-pin class="w-4 h-4 text-gray-400" />
                        Assignment
                    </h3>
                    @if ($bin->currentAssignment)
                        <a href="{{ route('admin.outlets.show', $bin->currentAssignment->outlet) }}" class="flex items-center gap-3 p-3 rounded-xl bg-emerald-50 border border-emerald-100 hover:bg-emerald-100/70 transition-colors">
                            <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                                <x-heroicon-s-building-storefront class="w-5 h-5 text-emerald-600" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-emerald-700 truncate">{{ $bin->currentAssignment->outlet->name }}</p>
                                <p class="text-xs text-emerald-600/60">Since {{ $bin->currentAssignment->assigned_at->format('d M Y') }}</p>
                            </div>
                        </a>
                        <form action="{{ route('admin.bins.unassign', $bin) }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="text-xs text-gray-400 hover:text-red-500 transition-colors flex items-center gap-1">
                                <x-heroicon-o-x-mark class="w-3 h-3" />
                                Unassign
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.bins.assign', $bin) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <x-dropdown.select
                                    name="outlet_id"
                                    placeholder="Select outlet..."
                                    :options="$outlets->pluck('name', 'id')->toArray()"
                                    :searchable="true"
                                    searchPlaceholder="Search outlets..."
                                />
                            </div>
                            <x-button type="submit" class="w-full justify-center">
                                <x-heroicon-o-link class="w-4 h-4" />
                                Assign
                            </x-button>
                        </form>
                    @endif
                </div>

                {{-- Assignment History --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-1.5">
                        <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                        History
                    </h3>
                    @if ($bin->assignments->isEmpty())
                        <p class="text-sm text-gray-400">No history</p>
                    @else
                        <div class="space-y-1.5">
                            @foreach ($bin->assignments->take(5) as $assignment)
                                <div class="flex items-center justify-between text-sm py-1 {{ !$assignment->unassigned_at ? 'text-emerald-700 font-medium' : 'text-gray-500' }}">
                                    <span class="truncate">{{ $assignment->outlet->name }}</span>
                                    <span class="text-xs text-gray-400 shrink-0 ml-2 font-mono">
                                        {{ $assignment->assigned_at->format('M Y') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-1.5">
                        <x-heroicon-o-cog-6-tooth class="w-4 h-4 text-gray-400" />
                        Actions
                    </h3>
                    <div class="space-y-0.5">
                        <a href="{{ route('admin.bins.analytics', $bin) }}" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                            <x-heroicon-o-chart-bar class="w-4 h-4 text-gray-400" />
                            View Analytics
                        </a>
                        <a href="{{ route('admin.bins.detections', $bin) }}" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                            <x-heroicon-o-camera class="w-4 h-4 text-gray-400" />
                            All Detections
                        </a>
                        <a href="{{ route('admin.bins.pickups', $bin) }}" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                            <x-heroicon-o-truck class="w-4 h-4 text-gray-400" />
                            Pickup History
                        </a>
                        <a href="{{ route('admin.bins.edit', $bin) }}" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                            <x-heroicon-o-pencil-square class="w-4 h-4 text-gray-400" />
                            Edit Bin
                        </a>
                        <form action="{{ route('admin.bins.destroy', $bin) }}" method="POST" onsubmit="return confirm('Delete this bin? This action can be undone (soft delete).')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm text-red-500 hover:bg-red-50 transition-colors w-full">
                                <x-heroicon-o-trash class="w-4 h-4" />
                                Delete Bin
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart.js --}}
    @if (!empty($wasteTypeChart))
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const wasteData = @json($wasteTypeChart);
                const wasteLabels = {
                    paper_cup: 'Paper Cup', plastic_cup: 'Plastic Cup', lid: 'Lid',
                    straw: 'Straw', napkin: 'Napkin', liquid_waste: 'Liquid Waste'
                };
                const wasteColors = {
                    paper_cup: '#f59e0b', plastic_cup: '#3b82f6', lid: '#6b7280',
                    straw: '#ec4899', napkin: '#f97316', liquid_waste: '#06b6d4'
                };

                new Chart(document.getElementById('binWasteChart'), {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(wasteData).map(k => wasteLabels[k] || k),
                        datasets: [{
                            data: Object.values(wasteData),
                            backgroundColor: Object.keys(wasteData).map(k => wasteColors[k] || '#9ca3af'),
                            borderWidth: 0,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { padding: 12, usePointStyle: true, pointStyle: 'circle', font: { size: 11, family: 'Inter' } }
                            }
                        }
                    }
                });
            });
        </script>
    @endif
</x-layouts.admin>

<x-layouts.store-owner title="Store Dashboard">
    <x-slot:header>
        Dashboard
    </x-slot:header>

    @php $brandColor = $brand?->primary_color ?? '#10b981'; @endphp

    {{-- Brand welcome header --}}
    <div class="mb-6 p-5 rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" style="background-color: {{ $brandColor }}15;">
                <x-heroicon-s-building-storefront class="w-6 h-6" style="color: {{ $brandColor }};" />
            </div>
            <div class="min-w-0 flex-1">
                @if ($isHQ && $outlets->count() > 1 && ! $selectedOutlet)
                    <h2 class="text-lg font-bold text-gray-900">{{ $brand->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $outlets->count() }} outlets</p>
                @else
                    <h2 class="text-lg font-bold text-gray-900">{{ $outlet->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $brand ? $brand->name . ' · ' : '' }}{{ $outlet->address }}</p>
                @endif
            </div>
            @if ($brand)
                <div class="hidden sm:flex items-center gap-3 shrink-0">
                    <div class="text-right">
                        <p class="text-xs text-gray-400">Points Multiplier</p>
                        <p class="text-lg font-bold" style="color: {{ $brandColor }};">{{ $brand->points_multiplier }}x</p>
                    </div>
                    <div class="w-px h-10 bg-gray-200"></div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400">Rewards Budget</p>
                        <p class="text-lg font-bold text-gray-900">{{ number_format($brand->rewards_budget) }}<span class="text-xs font-normal text-gray-400 ml-0.5">pts</span></p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Outlet selector (multi-outlet only) --}}
    @if ($outlets->count() > 1)
        <div class="mb-6 flex flex-wrap items-center gap-1.5">
            <a
                href="{{ route('store.dashboard') }}"
                class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-medium transition-colors {{ ! $selectedOutlet ? 'bg-emerald-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}"
            >
                All Outlets
            </a>
            @foreach ($outlets as $o)
                <a
                    href="{{ route('store.dashboard', ['outlet' => $o->id]) }}"
                    class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-medium transition-colors {{ $selectedOutlet?->id === $o->id ? 'bg-emerald-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}"
                >
                    {{ $o->name }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- Stats grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-xs text-gray-400 mb-1">Today</p>
            <p class="text-2xl font-bold text-gray-900">{{ $todayDetections }}</p>
            <p class="text-xs text-gray-400 mt-1">items recycled</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-xs text-gray-400 mb-1">This Week</p>
            <p class="text-2xl font-bold text-gray-900">{{ $weekDetections }}</p>
            <p class="text-xs text-gray-400 mt-1">items recycled</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-xs text-gray-400 mb-1">Unique Recyclers</p>
            <p class="text-2xl font-bold text-gray-900">{{ $uniqueRecyclers }}</p>
            <p class="text-xs text-gray-400 mt-1">this month</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-xs text-gray-400 mb-1">Redemptions</p>
            <p class="text-2xl font-bold text-gray-900">{{ $redemptionCount }}</p>
            <p class="text-xs text-gray-400 mt-1">this month</p>
        </div>
    </div>

    {{-- Brand loyalty breakdown (cups only) --}}
    @if ($brand && ($brandMatchCups + $competitorCups + $unidentifiedCups) > 0)
        @php
            $totalCups = $brandMatchCups + $competitorCups + $unidentifiedCups;
            $matchPct = $totalCups > 0 ? round($brandMatchCups / $totalCups * 100) : 0;
            $competitorPct = $totalCups > 0 ? round($competitorCups / $totalCups * 100) : 0;
            $unidentifiedPct = 100 - $matchPct - $competitorPct;
        @endphp
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-1.5">
                <x-heroicon-o-finger-print class="w-4 h-4 text-gray-400" />
                Cup Brand Loyalty (This Month)
            </h3>
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="text-center p-3 rounded-lg bg-emerald-50 border border-emerald-100">
                    <p class="text-2xl font-bold text-emerald-700">{{ $brandMatchCups }}</p>
                    <p class="text-[11px] text-emerald-600 mt-0.5">{{ $brand->name }} cups</p>
                </div>
                <div class="text-center p-3 rounded-lg bg-red-50 border border-red-100">
                    <p class="text-2xl font-bold text-red-700">{{ $competitorCups }}</p>
                    <p class="text-[11px] text-red-600 mt-0.5">Competitor cups</p>
                </div>
                <div class="text-center p-3 rounded-lg bg-gray-50 border border-gray-100">
                    <p class="text-2xl font-bold text-gray-600">{{ $unidentifiedCups }}</p>
                    <p class="text-[11px] text-gray-500 mt-0.5">Unidentified</p>
                </div>
            </div>
            {{-- Stacked bar --}}
            <div class="h-3 rounded-full overflow-hidden flex bg-gray-100">
                @if ($matchPct > 0)
                    <div class="h-full bg-emerald-500 transition-all duration-500" style="width: {{ $matchPct }}%"></div>
                @endif
                @if ($competitorPct > 0)
                    <div class="h-full bg-red-400 transition-all duration-500" style="width: {{ $competitorPct }}%"></div>
                @endif
                @if ($unidentifiedPct > 0)
                    <div class="h-full bg-gray-300 transition-all duration-500" style="width: {{ $unidentifiedPct }}%"></div>
                @endif
            </div>
            <div class="flex items-center justify-between mt-2 text-[11px] text-gray-400">
                <span>{{ $matchPct }}% brand match</span>
                <span>{{ $totalCups }} total cups</span>
            </div>
        </div>
    @endif

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Waste breakdown --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Waste Breakdown (This Month)</h3>
            @if (empty($wasteBreakdown))
                <p class="text-sm text-gray-400 py-6 text-center">No data yet</p>
            @else
                @php
                    $total = array_sum($wasteBreakdown);
                    $wasteColors = [
                        'paper_cup' => '#10b981',
                        'plastic_cup' => '#3b82f6',
                        'lid' => '#8b5cf6',
                        'straw' => '#f59e0b',
                        'napkin' => '#6b7280',
                        'liquid_waste' => '#06b6d4',
                    ];
                @endphp
                <div class="space-y-3">
                    @foreach ($wasteBreakdown as $type => $count)
                        @php
                            $pct = $total > 0 ? round($count / $total * 100) : 0;
                            $color = $wasteColors[$type] ?? '#6b7280';
                            $label = ucwords(str_replace('_', ' ', $type));
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-medium text-gray-700">{{ $label }}</span>
                                <span class="text-gray-400">{{ $count }} ({{ $pct }}%)</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" style="width: {{ $pct }}%; background-color: {{ $color }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Bins --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Your Bins</h3>
            @php
                $binOutlets = $selectedOutlet ? collect([$selectedOutlet]) : $outlets;
                $hasBins = $binOutlets->contains(fn ($o) => $o->bins->isNotEmpty());
            @endphp
            @if (! $hasBins)
                <p class="text-sm text-gray-400 py-6 text-center">No bins assigned yet</p>
            @else
                <div class="space-y-4">
                    @foreach ($binOutlets as $binOutlet)
                        @if ($binOutlet->bins->isEmpty())
                            @continue
                        @endif
                        @if (! $selectedOutlet && $outlets->count() > 1)
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide {{ ! $loop->first ? 'mt-2' : '' }}">{{ $binOutlet->name }}</p>
                        @endif
                        <div class="space-y-2.5">
                            @foreach ($binOutlet->bins as $bin)
                                <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 bg-gray-50/50">
                                    {{-- Fill gauge --}}
                                    <div class="shrink-0 w-10 h-10 relative flex items-center justify-center">
                                        @php
                                            $fill = $bin->fill_level;
                                            $gaugeColor = $fill >= 80 ? '#ef4444' : ($fill >= 50 ? '#eab308' : '#10b981');
                                            $circumference = 2 * M_PI * 15 * 0.75;
                                            $offset = $circumference - ($circumference * $fill / 100);
                                        @endphp
                                        <svg width="40" height="40" viewBox="0 0 40 40" class="transform rotate-[135deg]">
                                            <circle cx="20" cy="20" r="15" fill="none" stroke="#f3f4f6" stroke-width="3"
                                                stroke-dasharray="{{ $circumference }} {{ 2 * M_PI * 15 }}" stroke-linecap="round" />
                                            <circle cx="20" cy="20" r="15" fill="none"
                                                stroke="{{ $gaugeColor }}" stroke-width="3"
                                                stroke-dasharray="{{ $circumference }} {{ 2 * M_PI * 15 }}"
                                                stroke-dashoffset="{{ $offset }}"
                                                stroke-linecap="round" />
                                        </svg>
                                        <span class="absolute text-[9px] font-bold" style="color: {{ $gaugeColor }};">{{ $fill }}%</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">{{ $bin->serial_number }}</p>
                                        <p class="text-xs text-gray-400">{{ $bin->status->label() }}</p>
                                    </div>
                                    @if ($bin->last_seen_at && $bin->last_seen_at->diffInSeconds(now()) < 60)
                                        <span class="relative flex h-2 w-2 shrink-0" title="Online">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Quick links --}}
    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('store.rewards.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-white border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
            <x-heroicon-o-gift class="w-4 h-4 text-gray-400" />
            Manage Rewards
            @if ($activeRewards > 0)
                <span class="text-[10px] font-semibold rounded-full bg-emerald-100 text-emerald-700 px-1.5 py-0.5">{{ $activeRewards }}</span>
            @endif
        </a>
        <a href="{{ route('store.analytics') }}" class="inline-flex items-center gap-2 rounded-lg bg-white border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
            <x-heroicon-o-chart-bar class="w-4 h-4 text-gray-400" />
            View Analytics
        </a>
    </div>
</x-layouts.store-owner>

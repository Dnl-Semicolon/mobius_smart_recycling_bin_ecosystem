<x-layouts.agency title="Analytics">
    <x-slot:header>Analytics</x-slot:header>

    {{-- Daily completion chart --}}
    <x-card class="p-6 mb-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Daily Route Completions (14 days)</h2>
        @if ($dailyRoutes->isEmpty())
            <p class="text-sm text-gray-400 text-center py-8">No route data available</p>
        @else
            <div class="flex items-end gap-1 h-40">
                @php $maxVal = max($dailyRoutes->values()->toArray()); @endphp
                @foreach ($dailyRoutes as $date => $count)
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-[10px] text-gray-500">{{ $count }}</span>
                        <div
                            class="w-full bg-indigo-500 rounded-t"
                            style="height: {{ $maxVal > 0 ? ($count / $maxVal) * 100 : 0 }}%"
                        ></div>
                        <span class="text-[9px] text-gray-400 -rotate-45 origin-top-left">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>

    {{-- Collector performance --}}
    <x-card class="p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Collector Performance</h2>
        @if ($collectorStats->isEmpty())
            <p class="text-sm text-gray-400 text-center py-8">No performance data yet</p>
        @else
            <div class="space-y-3">
                @foreach ($collectorStats as $stat)
                    <div class="flex items-center gap-3 py-2">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                            <span class="text-xs font-semibold text-indigo-700">{{ strtoupper(substr($stat->collector?->name ?? '?', 0, 1)) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $stat->collector?->name ?? 'Unknown' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900">{{ $stat->routes_completed }}</p>
                            <p class="text-[10px] text-gray-500">routes</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>
</x-layouts.agency>

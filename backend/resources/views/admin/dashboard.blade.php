<x-layouts.admin title="Admin Dashboard">
    <x-slot:header>
        Dashboard
    </x-slot:header>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-8">
        {{-- Total Outlets --}}
        <x-card variant="glass" class="p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                    <x-heroicon-s-building-storefront class="w-6 h-6 text-blue-600" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Outlets</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['outlets']['total'] }}</p>
                    <p class="text-xs text-gray-400">{{ $stats['outlets']['active'] }} active</p>
                </div>
            </div>
        </x-card>

        {{-- Total Bins --}}
        <x-card variant="glass" class="p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                    <x-heroicon-s-archive-box class="w-6 h-6 text-emerald-600" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Bins</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['bins']['total'] }}</p>
                    <p class="text-xs text-gray-400">{{ $stats['bins']['assigned'] }} assigned</p>
                </div>
            </div>
        </x-card>

        {{-- Ready for Pickup --}}
        <x-card variant="glass" class="p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl {{ $stats['bins']['ready_for_pickup'] > 0 ? 'bg-red-50' : 'bg-gray-50' }} flex items-center justify-center shrink-0">
                    <x-heroicon-s-exclamation-triangle class="w-6 h-6 {{ $stats['bins']['ready_for_pickup'] > 0 ? 'text-red-500' : 'text-gray-400' }}" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Pickup</p>
                    <p class="text-3xl font-bold {{ $stats['bins']['ready_for_pickup'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $stats['bins']['ready_for_pickup'] }}
                    </p>
                    <p class="text-xs text-gray-400">bins at 80%+</p>
                </div>
            </div>
        </x-card>

        {{-- Today's Detections --}}
        <x-card variant="glass" class="p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center shrink-0">
                    <x-heroicon-s-eye class="w-6 h-6 text-violet-600" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Today</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['detections']['today'] }}</p>
                    <p class="text-xs text-gray-400">detections</p>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
        {{-- Waste Type Distribution --}}
        <x-card variant="glass" class="p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-1.5">
                <x-heroicon-o-chart-pie class="w-4 h-4 text-gray-400" />
                Waste Types
            </h3>
            <div class="flex justify-center">
                <canvas id="wasteTypeChart" width="220" height="220"></canvas>
            </div>
        </x-card>

        {{-- Detections This Week --}}
        <x-card variant="glass" class="p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-1.5">
                <x-heroicon-o-chart-bar class="w-4 h-4 text-gray-400" />
                Detections This Week
            </h3>
            <canvas id="weeklyChart" height="220"></canvas>
        </x-card>

        {{-- Pickup Status --}}
        <x-card variant="glass" class="p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-1.5">
                <x-heroicon-o-truck class="w-4 h-4 text-gray-400" />
                Pickup Requests
            </h3>
            <div class="flex justify-center">
                <canvas id="pickupChart" width="220" height="220"></canvas>
            </div>
        </x-card>
    </div>

    {{-- Bins Needing Pickup --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-3">
            <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-red-500" />
            <h2 class="text-sm font-semibold text-gray-900">Bins Needing Pickup</h2>
        </div>
        @if ($binsNeedingPickup->isEmpty())
            <x-card variant="subtle" class="text-center py-8">
                <x-heroicon-o-check-circle class="w-8 h-8 text-emerald-400 mx-auto mb-2" />
                <p class="text-gray-400">All bins are below threshold</p>
            </x-card>
        @else
            <div class="space-y-2">
                @foreach ($binsNeedingPickup as $bin)
                    <x-card variant="glass" class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ $bin->serial_number }}</p>
                                <p class="text-xs text-gray-500">
                                    @if ($bin->currentAssignment)
                                        {{ $bin->currentAssignment->outlet->name }}
                                    @else
                                        Unassigned
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-24 h-2.5 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-red-500 rounded-full" style="width: {{ $bin->fill_level }}%"></div>
                                </div>
                                <span class="text-sm font-bold text-red-600 w-10 text-right">{{ $bin->fill_level }}%</span>
                            </div>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Recent Detections --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-3">
            <x-heroicon-o-eye class="w-4 h-4 text-violet-500" />
            <h2 class="text-sm font-semibold text-gray-900">Recent Detections</h2>
        </div>
        @if ($recentDetections->isEmpty())
            <x-card variant="subtle" class="text-center py-8">
                <x-heroicon-o-camera class="w-8 h-8 text-gray-300 mx-auto mb-2" />
                <p class="text-gray-400">No detections yet</p>
            </x-card>
        @else
            <div class="space-y-2">
                @foreach ($recentDetections as $detection)
                    <x-card variant="glass" class="p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gray-50 flex items-center justify-center">
                                    <x-heroicon-s-beaker class="w-5 h-5 {{ $detection->waste_type->iconColor() }}" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $detection->waste_type->label() }}</p>
                                    <p class="text-xs text-gray-500">{{ $detection->bin->serial_number }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs bg-gray-100 text-gray-600 rounded-full px-2.5 py-0.5 font-medium">
                                    {{ $detection->confidence }}%
                                </span>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $detection->detected_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartDefaults = {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 16, usePointStyle: true, pointStyle: 'circle', font: { size: 11, family: 'Inter' } }
                    }
                }
            };

            // Waste Type Doughnut
            const wasteData = @json($chartData['wasteTypes']);
            const wasteLabels = {
                paper_cup: 'Paper Cup', plastic_cup: 'Plastic Cup', lid: 'Lid',
                straw: 'Straw', napkin: 'Napkin', liquid_waste: 'Liquid Waste'
            };
            const wasteColors = {
                paper_cup: '#f59e0b', plastic_cup: '#3b82f6', lid: '#6b7280',
                straw: '#ec4899', napkin: '#f97316', liquid_waste: '#06b6d4'
            };
            const wtLabels = Object.keys(wasteData).map(k => wasteLabels[k] || k);
            const wtColors = Object.keys(wasteData).map(k => wasteColors[k] || '#9ca3af');

            if (Object.keys(wasteData).length > 0) {
                new Chart(document.getElementById('wasteTypeChart'), {
                    type: 'doughnut',
                    data: {
                        labels: wtLabels,
                        datasets: [{ data: Object.values(wasteData), backgroundColor: wtColors, borderWidth: 0, hoverOffset: 8 }]
                    },
                    options: { ...chartDefaults, cutout: '60%' }
                });
            }

            // Weekly Detections Bar
            const weeklyData = @json($chartData['weeklyDetections']);
            new Chart(document.getElementById('weeklyChart'), {
                type: 'bar',
                data: {
                    labels: weeklyData.labels,
                    datasets: [{
                        data: weeklyData.data,
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 32
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11, family: 'Inter' } }, grid: { color: '#f3f4f6' } },
                        x: { ticks: { font: { size: 11, family: 'Inter' } }, grid: { display: false } }
                    }
                }
            });

            // Pickup Status Doughnut
            const pickupData = @json($chartData['pickupStatuses']);
            const pickupLabels = { pending: 'Pending', claimed: 'Claimed', completed: 'Completed' };
            const pickupColors = { pending: '#f59e0b', claimed: '#3b82f6', completed: '#10b981' };
            const pLabels = Object.keys(pickupData).map(k => pickupLabels[k] || k);
            const pColors = Object.keys(pickupData).map(k => pickupColors[k] || '#9ca3af');

            if (Object.keys(pickupData).length > 0) {
                new Chart(document.getElementById('pickupChart'), {
                    type: 'doughnut',
                    data: {
                        labels: pLabels,
                        datasets: [{ data: Object.values(pickupData), backgroundColor: pColors, borderWidth: 0, hoverOffset: 8 }]
                    },
                    options: { ...chartDefaults, cutout: '60%' }
                });
            }
        });
    </script>
</x-layouts.admin>

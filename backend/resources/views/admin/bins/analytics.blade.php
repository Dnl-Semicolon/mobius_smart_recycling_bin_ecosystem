<x-layouts.admin :title="$bin->serial_number . ' — Analytics'">
    <x-slot:back>
        <x-back-button href="{{ route('admin.bins.show', $bin) }}" />
    </x-slot:back>

    <x-slot:header>
        {{ $bin->serial_number }}
    </x-slot:header>

    {{-- Hero Bar (compact) --}}
    <x-card class="!p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <x-bin-gauge :value="$bin->fill_level" :size="48" />
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $bin->serial_number }}</p>
                    <p class="text-xs text-gray-500">{{ $bin->currentAssignment?->outlet->name ?? 'Unassigned' }}</p>
                </div>
            </div>
            <x-bin-tab-nav :bin="$bin" active="analytics" />
        </div>
    </x-card>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
        <x-card variant="glass" class="!p-4 text-center">
            <p class="text-xs text-gray-400">Total Detections</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_detections']) }}</p>
        </x-card>
        <x-card variant="glass" class="!p-4 text-center">
            <p class="text-xs text-gray-400">Waste Types Seen</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['unique_waste_types'] }}</p>
        </x-card>
        <x-card variant="glass" class="!p-4 text-center">
            <p class="text-xs text-gray-400">Avg Confidence</p>
            <p class="text-2xl font-bold {{ $stats['avg_confidence'] >= 85 ? 'text-emerald-600' : ($stats['avg_confidence'] >= 70 ? 'text-yellow-600' : 'text-gray-600') }}">
                {{ $stats['avg_confidence'] ?: '—' }}%
            </p>
        </x-card>
        <x-card variant="glass" class="!p-4 text-center">
            <p class="text-xs text-gray-400">Total Pickups</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_pickups'] }}</p>
        </x-card>
        <x-card variant="glass" class="!p-4 text-center">
            <p class="text-xs text-gray-400">Completed</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $stats['completed_pickups'] }}</p>
        </x-card>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Waste Type Distribution --}}
        <x-card variant="glass" class="!p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-1.5">
                <x-heroicon-o-chart-pie class="w-4 h-4 text-gray-400" />
                Waste Type Distribution
            </h3>
            @if (empty($chartData['wasteTypes']))
                <div class="flex flex-col items-center justify-center py-12">
                    <x-heroicon-o-chart-pie class="w-12 h-12 text-gray-200 mb-3" />
                    <p class="text-gray-400">No detection data yet</p>
                </div>
            @else
                <div class="flex justify-center">
                    <canvas id="wasteTypeChart" width="280" height="280"></canvas>
                </div>
            @endif
        </x-card>

        {{-- Weekly Detections --}}
        <x-card variant="glass" class="!p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-1.5">
                <x-heroicon-o-chart-bar class="w-4 h-4 text-gray-400" />
                Detections This Week
            </h3>
            <canvas id="weeklyChart" height="280"></canvas>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Confidence Distribution --}}
        <x-card variant="glass" class="!p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-1.5">
                <x-heroicon-o-signal class="w-4 h-4 text-gray-400" />
                Confidence Distribution
            </h3>
            <canvas id="confidenceChart" height="220"></canvas>
        </x-card>

        {{-- Fill Level Trend (Hardcoded — Intent) --}}
        <x-card variant="glass" class="!p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-1.5">
                <x-heroicon-o-arrow-trending-up class="w-4 h-4 text-gray-400" />
                Fill Level Trend
                <span class="text-xs font-normal text-gray-400 ml-auto">Simulated</span>
            </h3>
            <canvas id="fillTrendChart" height="220"></canvas>
            <p class="text-xs text-gray-400 mt-3 flex items-center gap-1">
                <x-heroicon-o-information-circle class="w-3.5 h-3.5" />
                Real-time IoT tracking coming soon
            </p>
        </x-card>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartFont = { family: 'Inter', size: 11 };

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

            if (Object.keys(wasteData).length > 0) {
                new Chart(document.getElementById('wasteTypeChart'), {
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
                        cutout: '60%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { padding: 16, usePointStyle: true, pointStyle: 'circle', font: chartFont }
                            }
                        }
                    }
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
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: chartFont }, grid: { color: '#f3f4f6' } },
                        x: { ticks: { font: chartFont }, grid: { display: false } }
                    }
                }
            });

            // Confidence Distribution
            const confData = @json($chartData['confidenceDistribution']);
            new Chart(document.getElementById('confidenceChart'), {
                type: 'bar',
                data: {
                    labels: confData.labels,
                    datasets: [{
                        data: confData.data,
                        backgroundColor: ['#ef4444', '#f59e0b', '#eab308', '#10b981', '#059669'],
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: chartFont }, grid: { color: '#f3f4f6' } },
                        x: { ticks: { font: chartFont }, grid: { display: false } }
                    }
                }
            });

            // Fill Level Trend (Simulated)
            const currentFill = {{ $bin->fill_level }};
            const trendData = [
                Math.max(0, currentFill - 45 + Math.floor(Math.random() * 10)),
                Math.max(0, currentFill - 35 + Math.floor(Math.random() * 10)),
                Math.max(0, currentFill - 20 + Math.floor(Math.random() * 10)),
                5, // Pickup happened — reset
                Math.max(0, currentFill - 30 + Math.floor(Math.random() * 10)),
                Math.max(0, currentFill - 15 + Math.floor(Math.random() * 10)),
                currentFill
            ];

            new Chart(document.getElementById('fillTrendChart'), {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Today'],
                    datasets: [{
                        data: trendData,
                        borderColor: currentFill >= 80 ? '#ef4444' : '#10b981',
                        backgroundColor: currentFill >= 80 ? 'rgba(239,68,68,0.1)' : 'rgba(16,185,129,0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderWidth: 2,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%', font: chartFont }, grid: { color: '#f3f4f6' } },
                        x: { ticks: { font: chartFont }, grid: { display: false } }
                    }
                }
            });
        });
    </script>
</x-layouts.admin>

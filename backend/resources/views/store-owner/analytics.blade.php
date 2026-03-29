<x-layouts.store-owner title="Analytics">
    <x-slot:header>
        Analytics
    </x-slot:header>

    <div class="space-y-6">
        {{-- Daily detections chart --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Daily Recycling Activity (Last 14 Days)</h3>
            @if (empty($dailyDetections))
                <p class="text-sm text-gray-400 py-8 text-center">No data yet</p>
            @else
                <div class="h-48">
                    <canvas id="dailyChart"></canvas>
                </div>
            @endif
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            {{-- Waste breakdown --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Waste Type Distribution (30 Days)</h3>
                @if (empty($wasteBreakdown))
                    <p class="text-sm text-gray-400 py-8 text-center">No data yet</p>
                @else
                    <div class="h-48 flex items-center justify-center">
                        <canvas id="wasteChart"></canvas>
                    </div>
                @endif
            </div>

            {{-- Peak hours --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Peak Hours (30 Days)</h3>
                @if (empty($hourlyDistribution))
                    <p class="text-sm text-gray-400 py-8 text-center">No data yet</p>
                @else
                    <div class="h-48">
                        <canvas id="hourlyChart"></canvas>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if (!empty($dailyDetections) || !empty($wasteBreakdown) || !empty($hourlyDistribution))
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var brandColor = @js($outlet->brand?->primary_color ?? '#10b981');

            @if (!empty($dailyDetections))
            new Chart(document.getElementById('dailyChart'), {
                type: 'line',
                data: {
                    labels: @js(array_keys($dailyDetections)),
                    datasets: [{
                        label: 'Items Recycled',
                        data: @js(array_values($dailyDetections)),
                        borderColor: brandColor,
                        backgroundColor: brandColor + '20',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        x: { ticks: { maxTicksLimit: 7 } }
                    }
                }
            });
            @endif

            @if (!empty($wasteBreakdown))
            var wasteColors = {
                'paper_cup': '#10b981', 'plastic_cup': '#3b82f6', 'lid': '#8b5cf6',
                'straw': '#f59e0b', 'napkin': '#6b7280', 'liquid_waste': '#06b6d4'
            };
            var wasteData = @js($wasteBreakdown);
            new Chart(document.getElementById('wasteChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(wasteData).map(function(k) { return k.replace('_', ' '); }),
                    datasets: [{
                        data: Object.values(wasteData),
                        backgroundColor: Object.keys(wasteData).map(function(k) { return wasteColors[k] || '#6b7280'; }),
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } } }
                }
            });
            @endif

            @if (!empty($hourlyDistribution))
            var hourlyData = @js($hourlyDistribution);
            var hours = Array.from({length: 24}, function(_, i) { return String(i).padStart(2, '0'); });
            new Chart(document.getElementById('hourlyChart'), {
                type: 'bar',
                data: {
                    labels: hours.map(function(h) { return h + ':00'; }),
                    datasets: [{
                        label: 'Detections',
                        data: hours.map(function(h) { return hourlyData[h] || 0; }),
                        backgroundColor: brandColor + '60',
                        borderColor: brandColor,
                        borderWidth: 1,
                        borderRadius: 3,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        x: { ticks: { maxTicksLimit: 12, font: { size: 10 } } }
                    }
                }
            });
            @endif
        });
        </script>
    @endif
</x-layouts.store-owner>

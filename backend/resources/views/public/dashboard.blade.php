<x-layouts.app title="Community Dashboard — Mobius">
    <div class="min-h-screen bg-gray-50">
        {{-- Header --}}
        <header class="bg-white border-b border-gray-200/60">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center">
                        <x-heroicon-s-arrow-path class="w-5 h-5 text-white" />
                    </span>
                    <span class="font-bold text-gray-900 tracking-tight">Mobius</span>
                    <span class="text-xs text-gray-400 border-l border-gray-200 pl-3 ml-1">Community</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-400 hover:text-red-600 transition-colors">Sign out</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
            {{-- Welcome --}}
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Community Impact</h1>
                <p class="text-sm text-gray-500 mt-1">See how our recycling network is making a difference.</p>
            </div>

            {{-- Impact Stats --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-8">
                <div class="bg-white rounded-xl border border-gray-200/60 p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                            <x-heroicon-s-eye class="w-5 h-5 text-emerald-600" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Items Detected</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($impactStats['total_detections']) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200/60 p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                            <x-heroicon-s-truck class="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Pickups Done</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($impactStats['pickups_completed']) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200/60 p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                            <x-heroicon-s-archive-box class="w-5 h-5 text-amber-600" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Active Bins</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($impactStats['active_bins']) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200/60 p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                            <x-heroicon-s-building-storefront class="w-5 h-5 text-purple-600" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Outlets</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($impactStats['outlets_served']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Two-column layout: Activity + Chart --}}
            <div class="grid lg:grid-cols-5 gap-6">
                {{-- Recent Activity --}}
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-xl border border-gray-200/60 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100">
                            <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                                Recent Activity
                            </h2>
                        </div>
                        @if ($recentActivity->isEmpty())
                            <div class="p-8 text-center">
                                <x-heroicon-o-inbox class="w-10 h-10 text-gray-300 mx-auto mb-2" />
                                <p class="text-sm text-gray-500">No detections recorded yet.</p>
                            </div>
                        @else
                            <div class="divide-y divide-gray-50">
                                @foreach ($recentActivity as $activity)
                                    <div class="px-5 py-3 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center shrink-0">
                                                <x-heroicon-o-arrow-path class="w-4 h-4 text-emerald-600" />
                                            </span>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 capitalize">{{ $activity['waste_type'] }}</p>
                                                <p class="text-xs text-gray-500">{{ $activity['outlet'] }}</p>
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-400">{{ $activity['time'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Waste Breakdown Chart --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl border border-gray-200/60 p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <x-heroicon-o-chart-pie class="w-4 h-4 text-gray-400" />
                            Waste Breakdown
                        </h2>
                        @if (empty($wasteTypeDistribution))
                            <div class="py-8 text-center">
                                <p class="text-sm text-gray-500">No data yet.</p>
                            </div>
                        @else
                            <canvas id="wasteChart" class="w-full" style="max-height: 280px;"></canvas>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    @if (!empty($wasteTypeDistribution))
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const data = @json($wasteTypeDistribution);
                const labels = Object.keys(data).map(k => k.replace(/_/g, ' '));
                const values = Object.values(data);
                const colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

                new Chart(document.getElementById('wasteChart'), {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: colors.slice(0, labels.length),
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true } }
                        }
                    }
                });
            });
        </script>
    @endif
</x-layouts.app>

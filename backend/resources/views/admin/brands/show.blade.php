<x-layouts.admin :title="$brand->name">
    <x-slot:back>
        <x-back-button href="{{ route('admin.brands.index') }}" />
    </x-slot:back>

    <x-slot:header>
        {{ $brand->name }}
    </x-slot:header>

    @php
        $statusColors = [
            'approved' => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200/60',
            'pending' => 'bg-amber-100 text-amber-700 ring-1 ring-amber-200/60',
            'rejected' => 'bg-red-100 text-red-700 ring-1 ring-red-200/60',
        ];
    @endphp

    <div class="space-y-5">
        {{-- Brand info card --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 lg:p-6">
            <div class="flex flex-col sm:flex-row sm:items-start gap-5">
                {{-- Logo / placeholder --}}
                <div class="shrink-0">
                    @if ($brand->logo_path)
                        <img src="{{ Storage::url($brand->logo_path) }}" alt="{{ $brand->name }}" class="w-16 h-16 rounded-xl object-cover ring-1 ring-black/5">
                    @else
                        <div class="w-16 h-16 rounded-xl flex items-center justify-center ring-1 ring-gray-200 bg-gray-50">
                            <x-heroicon-s-building-storefront class="w-8 h-8 text-gray-400" />
                        </div>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="text-xs font-semibold rounded-full px-2.5 py-1 {{ $statusColors[$brand->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($brand->status) }}
                        </span>
                    </div>

                    <h2 class="text-lg font-bold text-gray-900">{{ $brand->name }}</h2>

                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Multiplier</p>
                            <p class="font-semibold text-gray-900 mt-0.5">{{ $brand->point_multiplier }}x</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">HQ Admin</p>
                            @if ($brand->adminUser)
                                <p class="font-semibold text-gray-900 mt-0.5">{{ $brand->adminUser->name }}</p>
                                <p class="text-xs text-gray-500">{{ $brand->adminUser->email }}</p>
                            @else
                                <p class="text-gray-400 mt-0.5">No HQ admin</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            @php
                $statCards = [
                    ['label' => 'Total Outlets', 'value' => $stats['total_outlets'], 'icon' => 'heroicon-o-building-storefront', 'color' => 'bg-blue-100 text-blue-600 ring-blue-200/60'],
                    ['label' => 'Total Bins', 'value' => $stats['total_bins'], 'icon' => 'heroicon-o-archive-box', 'color' => 'bg-emerald-100 text-emerald-600 ring-emerald-200/60'],
                    ['label' => 'Total Staff', 'value' => $stats['total_staff'], 'icon' => 'heroicon-o-users', 'color' => 'bg-violet-100 text-violet-600 ring-violet-200/60'],
                    ['label' => "Today's Detections", 'value' => $stats['today_detections'], 'icon' => 'heroicon-o-eye', 'color' => 'bg-amber-100 text-amber-600 ring-amber-200/60'],
                    ['label' => "Month's Detections", 'value' => $stats['month_detections'], 'icon' => 'heroicon-o-chart-bar', 'color' => 'bg-rose-100 text-rose-600 ring-rose-200/60'],
                    ['label' => 'Active Rewards', 'value' => $stats['active_rewards'], 'icon' => 'heroicon-o-gift', 'color' => 'bg-cyan-100 text-cyan-600 ring-cyan-200/60'],
                ];
            @endphp

            @foreach ($statCards as $card)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="w-8 h-8 rounded-lg ring-1 flex items-center justify-center {{ $card['color'] }}">
                            <x-dynamic-component :component="$card['icon']" class="w-4 h-4" />
                        </div>
                        <p class="text-xs font-medium text-gray-500">{{ $card['label'] }}</p>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Outlets section --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 lg:p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center ring-1 ring-blue-200/60">
                        <x-heroicon-s-building-storefront class="w-4 h-4 text-blue-600" />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Outlets</h3>
                </div>
                <span class="text-xs font-semibold text-gray-400 bg-gray-100 rounded-full px-2 py-0.5">{{ $brand->outlets->count() }}</span>
            </div>

            @if ($brand->outlets->isEmpty())
                <div class="rounded-xl bg-gray-50 border border-gray-100 py-10 text-center">
                    <x-heroicon-o-building-storefront class="w-8 h-8 text-gray-300 mx-auto mb-2" />
                    <p class="text-sm text-gray-400 font-medium">No outlets</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($brand->outlets as $outlet)
                        <div class="rounded-xl border border-gray-100 p-4 hover:bg-gray-50/50 transition-colors">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.outlets.show', $outlet) }}" class="font-semibold text-gray-900 hover:text-emerald-600 transition-colors">
                                        {{ $outlet->name }}
                                    </a>
                                    <p class="text-sm text-gray-500 mt-0.5 truncate">{{ $outlet->address }}</p>
                                </div>
                                <span class="text-xs text-gray-400 flex items-center gap-1 shrink-0">
                                    <x-heroicon-o-archive-box class="w-3 h-3" />
                                    {{ $outlet->bins->count() }} {{ Str::plural('bin', $outlet->bins->count()) }}
                                </span>
                            </div>

                            @if ($outlet->managers->isNotEmpty())
                                <div class="mt-2 flex items-center gap-1.5 flex-wrap">
                                    <x-heroicon-o-users class="w-3 h-3 text-gray-400 shrink-0" />
                                    @foreach ($outlet->managers as $manager)
                                        <span class="text-xs text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ $manager->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Rewards section --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 lg:p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-cyan-100 flex items-center justify-center ring-1 ring-cyan-200/60">
                        <x-heroicon-s-gift class="w-4 h-4 text-cyan-600" />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Rewards</h3>
                </div>
                <span class="text-xs font-semibold text-gray-400 bg-gray-100 rounded-full px-2 py-0.5">{{ $brand->rewards->count() }}</span>
            </div>

            @if ($brand->rewards->isEmpty())
                <div class="rounded-xl bg-gray-50 border border-gray-100 py-10 text-center">
                    <x-heroicon-o-gift class="w-8 h-8 text-gray-300 mx-auto mb-2" />
                    <p class="text-sm text-gray-400 font-medium">No rewards</p>
                </div>
            @else
                <div class="overflow-hidden rounded-lg border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/60">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Points Required</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($brand->rewards as $reward)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $reward->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-center tabular-nums">{{ number_format($reward->points_required) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-400 text-center">&mdash;</td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($reward->is_active)
                                            <span class="text-xs font-medium rounded-full px-2 py-0.5 bg-emerald-100 text-emerald-700">Active</span>
                                        @else
                                            <span class="text-xs font-medium rounded-full px-2 py-0.5 bg-gray-100 text-gray-500">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>

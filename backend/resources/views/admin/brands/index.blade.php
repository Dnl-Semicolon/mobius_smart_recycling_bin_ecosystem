<x-layouts.admin title="Brands">
    <x-slot:header>
        Brands
    </x-slot:header>

    {{-- Status filter tabs --}}
    @php
        $currentStatus = request('status', '');
        $statuses = [
            'approved' => ['label' => 'Approved', 'dot' => 'bg-emerald-500', 'dotRing' => 'ring-4 ring-emerald-500/20', 'on' => 'bg-emerald-50 text-emerald-700 border-emerald-200 shadow-sm shadow-emerald-500/10'],
            'pending' => ['label' => 'Pending', 'dot' => 'bg-amber-500', 'dotRing' => 'ring-4 ring-amber-500/20', 'on' => 'bg-amber-50 text-amber-700 border-amber-200 shadow-sm shadow-amber-500/10'],
            'rejected' => ['label' => 'Rejected', 'dot' => 'bg-red-500', 'dotRing' => 'ring-4 ring-red-500/20', 'on' => 'bg-red-50 text-red-700 border-red-200 shadow-sm shadow-red-500/10'],
        ];
    @endphp

    <div class="flex items-center gap-2.5 flex-wrap mb-6">
        <a
            href="{{ route('admin.brands.index') }}"
            class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium border {{ $currentStatus === '' ? 'bg-gray-900 text-white border-gray-900 shadow-sm' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 shadow-sm' }}"
        >
            All
            <span class="text-[11px] font-semibold rounded-full min-w-[20px] px-1.5 py-0.5 text-center leading-none {{ $currentStatus === '' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $brands->total() }}</span>
        </a>

        @foreach ($statuses as $value => $s)
            @php $isActive = $currentStatus === $value; @endphp
            <a
                href="{{ route('admin.brands.index', ['status' => $value]) }}"
                class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium border {{ $isActive ? $s['on'] : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 shadow-sm' }}"
            >
                <span class="w-2 h-2 rounded-full {{ $s['dot'] }} {{ $isActive ? $s['dotRing'] : '' }}"></span>
                {{ $s['label'] }}
            </a>
        @endforeach

        @if ($currentStatus)
            <a
                href="{{ route('admin.brands.index') }}"
                class="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-red-500 ml-1"
            >
                <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                Clear filters
            </a>
        @endif
    </div>

    @if ($brands->isEmpty())
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-building-storefront class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            @if ($currentStatus)
                <p class="text-gray-400">No brands match your filters</p>
                <a href="{{ route('admin.brands.index') }}" class="inline-flex items-center gap-1.5 mt-4 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                    Clear filters
                </a>
            @else
                <p class="text-gray-400">No brands yet</p>
            @endif
        </x-card>
    @else
        {{-- Brands table --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/60">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Brand</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Outlets</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Rewards</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Redemptions</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($brands as $brand)
                        @php
                            $statusColors = [
                                'approved' => 'bg-emerald-100 text-emerald-700',
                                'pending' => 'bg-amber-100 text-amber-700',
                                'rejected' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($brand->primary_color)
                                        <span class="w-3 h-3 rounded-full shrink-0 ring-2 ring-white shadow-sm" style="background: {{ $brand->primary_color }}"></span>
                                    @endif
                                    <span class="font-semibold text-gray-900">{{ $brand->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium rounded-full px-2.5 py-1 {{ $statusColors[$brand->status->value] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $brand->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-gray-600 tabular-nums">{{ $brand->outlets_count }}</td>
                            <td class="px-6 py-4 text-center text-sm text-gray-600 tabular-nums">{{ $brand->rewards_count }}</td>
                            <td class="px-6 py-4 text-center text-sm text-gray-600 tabular-nums">{{ $brand->redemptions_count }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.brands.show', $brand) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $brands->links() }}
        </div>
    @endif
</x-layouts.admin>

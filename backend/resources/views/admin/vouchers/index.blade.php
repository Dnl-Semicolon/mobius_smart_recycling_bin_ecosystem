<x-layouts.admin title="Vouchers">
    <x-slot:header>
        Vouchers
    </x-slot:header>

    <div x-data="{ tab: '{{ request('tab', 'templates') }}' }">
        {{-- Tabs --}}
        <div class="flex gap-1 bg-gray-100 rounded-xl p-1 mb-6">
            <button
                @click="tab = 'templates'"
                :class="tab === 'templates' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="flex-1 px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-150 cursor-pointer"
            >
                Templates
                <span class="ml-1 text-xs text-gray-400">({{ $templates->total() }})</span>
            </button>
            <button
                @click="tab = 'allocations'"
                :class="tab === 'allocations' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="flex-1 px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-150 cursor-pointer"
            >
                Allocations
                <span class="ml-1 text-xs text-gray-400">({{ $allocations->total() }})</span>
            </button>
            <button
                @click="tab = 'claims'"
                :class="tab === 'claims' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="flex-1 px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-150 cursor-pointer"
            >
                Claims
                <span class="ml-1 text-xs text-gray-400">({{ $claims->total() }})</span>
            </button>
        </div>

        {{-- Templates Tab --}}
        <div x-show="tab === 'templates'" x-cloak>
            @if ($templates->isEmpty())
                <x-card variant="subtle" class="text-center py-16">
                    <x-heroicon-o-gift class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                    <p class="text-gray-400">No voucher templates yet</p>
                    <p class="text-sm text-gray-400 mt-1">Voucher templates define the reward type, value, and point cost.</p>
                </x-card>
            @else
                <div class="space-y-3">
                    @foreach ($templates as $template)
                        @php
                            $typeColor = match($template->type) {
                                'discount' => 'bg-blue-100 text-blue-700',
                                'free_item' => 'bg-emerald-100 text-emerald-700',
                                'cashback' => 'bg-violet-100 text-violet-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <x-card class="p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0 mt-0.5">
                                        <x-heroicon-o-gift class="w-5 h-5 text-gray-400" />
                                    </div>
                                    <div class="min-w-0">
                                        <h2 class="font-semibold text-gray-900">{{ $template->name }}</h2>
                                        <p class="text-sm text-gray-500 mt-0.5">{{ $template->brand?->name ?? 'No brand' }}</p>
                                        <div class="text-xs text-gray-400 mt-1 space-y-0.5">
                                            <p>
                                                Value: RM{{ number_format($template->value, 2) }}
                                                <span class="text-gray-300 mx-0.5">&middot;</span>
                                                {{ $template->points_required }} pts required
                                            </p>
                                            <p>
                                                {{ $template->valid_from?->format('d M Y') }} &ndash; {{ $template->valid_until?->format('d M Y') }}
                                            </p>
                                            <p>
                                                {{ $template->allocations_count }} allocations
                                                <span class="text-gray-300 mx-0.5">&middot;</span>
                                                {{ $template->claims_count }} claims
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="shrink-0 flex flex-col items-end gap-1.5">
                                    <span class="text-xs font-medium rounded-full px-2.5 py-1 {{ $typeColor }}">
                                        {{ ucwords(str_replace('_', ' ', $template->type)) }}
                                    </span>
                                    @if ($template->is_active && $template->isCurrentlyValid())
                                        <span class="text-xs font-medium rounded-full px-2.5 py-1 bg-emerald-100 text-emerald-700">Active</span>
                                    @elseif ($template->is_active)
                                        <span class="text-xs font-medium rounded-full px-2.5 py-1 bg-amber-100 text-amber-700">Scheduled</span>
                                    @else
                                        <span class="text-xs font-medium rounded-full px-2.5 py-1 bg-gray-100 text-gray-600">Inactive</span>
                                    @endif
                                </div>
                            </div>
                        </x-card>
                    @endforeach
                </div>
                <div class="mt-6">{{ $templates->links() }}</div>
            @endif
        </div>

        {{-- Allocations Tab --}}
        <div x-show="tab === 'allocations'" x-cloak>
            @if ($allocations->isEmpty())
                <x-card variant="subtle" class="text-center py-16">
                    <x-heroicon-o-building-storefront class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                    <p class="text-gray-400">No voucher allocations yet</p>
                    <p class="text-sm text-gray-400 mt-1">Allocations distribute voucher quotas to specific outlets.</p>
                </x-card>
            @else
                <div class="space-y-3">
                    @foreach ($allocations as $allocation)
                        <x-card class="p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0 mt-0.5">
                                        <x-heroicon-o-building-storefront class="w-5 h-5 text-gray-400" />
                                    </div>
                                    <div class="min-w-0">
                                        <h2 class="font-semibold text-gray-900">{{ $allocation->voucherTemplate?->name ?? 'Unknown Template' }}</h2>
                                        <p class="text-sm text-gray-500 mt-0.5">{{ $allocation->outlet?->name ?? 'Unknown Outlet' }}</p>
                                        <div class="text-xs text-gray-400 mt-1">
                                            <p>
                                                Claimed: {{ $allocation->claimed_count }} / {{ $allocation->quota }}
                                                <span class="text-gray-300 mx-0.5">&middot;</span>
                                                {{ $allocation->valid_from?->format('d M Y') }} &ndash; {{ $allocation->valid_until?->format('d M Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    @if ($allocation->hasQuotaRemaining())
                                        <span class="text-xs font-medium rounded-full px-2.5 py-1 bg-emerald-100 text-emerald-700">Available</span>
                                    @else
                                        <span class="text-xs font-medium rounded-full px-2.5 py-1 bg-red-100 text-red-700">Exhausted</span>
                                    @endif
                                </div>
                            </div>
                        </x-card>
                    @endforeach
                </div>
                <div class="mt-6">{{ $allocations->links() }}</div>
            @endif
        </div>

        {{-- Claims Tab --}}
        <div x-show="tab === 'claims'" x-cloak>
            @if ($claims->isEmpty())
                <x-card variant="subtle" class="text-center py-16">
                    <x-heroicon-o-ticket class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                    <p class="text-gray-400">No voucher claims yet</p>
                    <p class="text-sm text-gray-400 mt-1">Claims appear when users redeem their points for vouchers.</p>
                </x-card>
            @else
                <div class="space-y-3">
                    @foreach ($claims as $claim)
                        @php
                            $statusColor = match($claim->status) {
                                'claimed' => 'bg-blue-100 text-blue-700',
                                'redeemed' => 'bg-emerald-100 text-emerald-700',
                                'expired' => 'bg-gray-100 text-gray-600',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <x-card class="p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0 mt-0.5">
                                        <x-heroicon-o-ticket class="w-5 h-5 text-gray-400" />
                                    </div>
                                    <div class="min-w-0">
                                        <h2 class="font-semibold text-gray-900">{{ $claim->voucherTemplate?->name ?? 'Unknown' }}</h2>
                                        <p class="text-sm text-gray-500 mt-0.5">
                                            {{ $claim->user?->name ?? 'Unknown User' }}
                                            <span class="text-gray-300 mx-0.5">&middot;</span>
                                            {{ $claim->points_spent }} pts spent
                                        </p>
                                        <div class="text-xs text-gray-400 mt-1">
                                            <p>
                                                Claimed: {{ $claim->claimed_at?->format('d M Y, H:i') ?? 'N/A' }}
                                                <span class="text-gray-300 mx-0.5">&middot;</span>
                                                Expires: {{ $claim->expires_at?->format('d M Y') ?? 'N/A' }}
                                            </p>
                                            @if ($claim->redeemed_at)
                                                <p>Redeemed: {{ $claim->redeemed_at->format('d M Y, H:i') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    <span class="text-xs font-medium rounded-full px-2.5 py-1 {{ $statusColor }}">
                                        {{ ucfirst($claim->status) }}
                                    </span>
                                </div>
                            </div>
                        </x-card>
                    @endforeach
                </div>
                <div class="mt-6">{{ $claims->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.admin>

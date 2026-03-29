<x-layouts.store-owner title="Rewards">
    <x-slot:header>
        Rewards
    </x-slot:header>

    @php $brandColor = $brand->primary_color ?? '#10b981'; @endphp

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background-color: {{ $brandColor }}15;">
                <x-heroicon-s-gift class="w-4 h-4" style="color: {{ $brandColor }};" />
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900">{{ $brand->name }} Rewards</p>
                <p class="text-xs text-gray-400">Budget: <span class="font-medium text-gray-600">{{ number_format($brand->rewards_budget) }} pts</span></p>
            </div>
        </div>
        <a href="{{ route('store.rewards.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.2),0_1px_2px_rgba(0,0,0,0.15)] hover:bg-emerald-700 active:bg-emerald-800 active:shadow-[inset_0_1px_3px_rgba(0,0,0,0.2)] transition-all duration-100">
            <x-heroicon-o-gift class="w-4 h-4 opacity-80" />
            New
        </a>
    </div>

    @if ($rewards->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm text-center py-16">
            <x-heroicon-o-gift class="w-12 h-12 text-gray-200 mx-auto mb-3" />
            <p class="text-gray-400 mb-4">No rewards created yet</p>
            <a href="{{ route('store.rewards.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                Create your first reward
            </a>
        </div>
    @else
        <div class="space-y-2">
            @foreach ($rewards as $reward)
                <div class="bg-white rounded-xl border border-gray-200/80 p-4 hover:shadow-md hover:border-emerald-200 transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background-color: {{ $brandColor }}10;">
                            <x-heroicon-s-gift class="w-5 h-5" style="color: {{ $brandColor }};" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $reward->name }}</h3>
                                @if (! $reward->active)
                                    <span class="text-[10px] font-semibold rounded-full bg-gray-100 text-gray-500 px-2 py-0.5">Inactive</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 truncate">{{ $reward->description ?? 'No description' }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-sm font-bold" style="color: {{ $brandColor }};">{{ $reward->points_cost }}<span class="text-xs font-normal text-gray-400 ml-0.5">pts</span></p>
                            <p class="text-[10px] text-gray-400 mt-0.5">
                                @if ($reward->stock === null)
                                    Unlimited
                                @else
                                    {{ $reward->stock }} left
                                @endif
                                · {{ $reward->redemptions_count }} redeemed
                            </p>
                        </div>
                        <a href="{{ route('store.rewards.edit', $reward) }}" class="shrink-0 p-2 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.store-owner>

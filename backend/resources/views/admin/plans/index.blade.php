<x-layouts.admin title="Plans">
    <x-slot:header>
        Plans
    </x-slot:header>

    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">{{ $plans->count() }} {{ Str::plural('plan', $plans->count()) }} configured</p>
        <a href="{{ route('admin.plans.create') }}"
           class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
            <x-heroicon-o-plus class="w-4 h-4" />
            New Plan
        </a>
    </div>

    @if ($plans->isEmpty())
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-rectangle-group class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-400">No plans configured</p>
            <p class="text-sm text-gray-400 mt-1">Create your first subscription plan to get started.</p>
        </x-card>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($plans as $plan)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex flex-col">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-gray-900">{{ $plan->name }}</h2>
                            @if ($plan->is_active)
                                <span class="text-xs font-medium rounded-full px-2 py-0.5 bg-emerald-100 text-emerald-700">Active</span>
                            @else
                                <span class="text-xs font-medium rounded-full px-2 py-0.5 bg-gray-100 text-gray-600">Inactive</span>
                            @endif
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0">
                            <x-heroicon-o-rectangle-group class="w-5 h-5 text-gray-400" />
                        </div>
                    </div>

                    {{-- Pricing --}}
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-2xl font-bold text-gray-900">RM{{ number_format($plan->price_monthly, 0) }}</span>
                        <span class="text-sm text-gray-400">/mo</span>
                    </div>
                    <p class="text-xs text-gray-500 mb-3">
                        RM{{ number_format($plan->price_yearly, 0) }}/year
                    </p>

                    @if ($plan->description)
                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $plan->description }}</p>
                    @endif

                    {{-- Features --}}
                    @if (is_array($plan->features) && count($plan->features) > 0)
                        <ul class="text-xs text-gray-600 space-y-1 mb-4 flex-1">
                            @foreach ($plan->features as $feature)
                                <li class="flex items-start gap-1.5">
                                    <x-heroicon-s-check class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" />
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="flex-1"></div>
                    @endif

                    {{-- Footer --}}
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100 mt-auto">
                        <span class="text-xs text-gray-400">
                            {{ $plan->subscriptions_count }} {{ Str::plural('subscriber', $plan->subscriptions_count) }}
                        </span>
                        <a href="{{ route('admin.plans.edit', $plan) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-100 transition-colors">
                            <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                            Edit
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.admin>

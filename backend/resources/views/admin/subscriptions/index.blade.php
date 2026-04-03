<x-layouts.admin title="Subscriptions">
    <x-slot:header>
        Subscriptions
    </x-slot:header>

    @if ($subscriptions->isEmpty())
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-credit-card class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-400">No subscriptions yet</p>
            <p class="text-sm text-gray-400 mt-1">Subscriptions appear when organizations sign up for a plan.</p>
        </x-card>
    @else
        <div class="space-y-3">
            @foreach ($subscriptions as $subscription)
                @php
                    $statusColor = match($subscription->status) {
                        'active' => 'bg-emerald-100 text-emerald-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                        'expired' => 'bg-gray-100 text-gray-600',
                        'trialing' => 'bg-blue-100 text-blue-700',
                        default => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <x-card class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0 mt-0.5">
                                <x-heroicon-o-credit-card class="w-5 h-5 text-gray-400" />
                            </div>
                            <div class="min-w-0">
                                <h2 class="font-semibold text-gray-900">
                                    {{ $subscription->organization->name ?? 'Unknown Org' }}
                                </h2>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    {{ $subscription->plan->name ?? 'Unknown Plan' }}
                                </p>
                                <div class="text-xs text-gray-400 mt-1 space-y-0.5">
                                    <p>
                                        Starts: {{ $subscription->starts_at?->format('d M Y') ?? 'N/A' }}
                                        <span class="text-gray-300 mx-0.5">·</span>
                                        Ends: {{ $subscription->ends_at?->format('d M Y') ?? 'N/A' }}
                                    </p>
                                    @if ($subscription->renews_at)
                                        <p>Renews: {{ $subscription->renews_at->format('d M Y') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="shrink-0">
                            <span class="text-xs font-medium rounded-full px-2.5 py-1 {{ $statusColor }}">
                                {{ ucfirst($subscription->status) }}
                            </span>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $subscriptions->links() }}
        </div>
    @endif
</x-layouts.admin>

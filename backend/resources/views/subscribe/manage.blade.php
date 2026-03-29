<x-layouts.app title="Manage Subscription">
    <div class="min-h-screen flex items-center justify-center bg-black px-4 py-16">
        <div class="w-full max-w-md">
            <h1 class="text-2xl font-semibold text-white text-center mb-8">Manage Subscription</h1>

            <div class="rounded-lg border border-gray-800 bg-gray-950 p-6">
                @if ($subscription && $subscription->active())
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-emerald-900/50 flex items-center justify-center">
                            <x-heroicon-s-check-circle class="w-5 h-5 text-emerald-400" />
                        </div>
                        <div>
                            <p class="text-white font-medium">Active Subscription</p>
                            <p class="text-xs text-gray-500">{{ $subscription->stripe_price }}</p>
                        </div>
                    </div>

                    @if ($subscription->onGracePeriod())
                        <div class="rounded-lg bg-amber-900/20 border border-amber-800/50 p-3 mb-4">
                            <p class="text-sm text-amber-300">Your subscription will end on {{ $subscription->ends_at->format('d M Y') }}.</p>
                        </div>
                    @endif

                    <div class="space-y-3">
                        <a href="{{ route('subscribe.billing-portal') }}"
                           class="flex items-center justify-center gap-2 w-full rounded-lg bg-white text-black font-medium py-2.5 text-sm hover:bg-gray-200 transition-colors">
                            <x-heroicon-o-credit-card class="w-4 h-4" />
                            Billing Portal
                        </a>

                        @unless ($subscription->canceled())
                            <form method="POST" action="{{ route('subscribe.cancel-subscription') }}">
                                @csrf
                                <button type="submit"
                                        class="flex items-center justify-center gap-2 w-full rounded-lg border border-red-800 text-red-400 font-medium py-2.5 text-sm hover:bg-red-900/20 transition-colors cursor-pointer"
                                        onclick="return confirm('Cancel your subscription?')">
                                    <x-heroicon-o-x-circle class="w-4 h-4" />
                                    Cancel Subscription
                                </button>
                            </form>
                        @endunless
                    </div>
                @else
                    <div class="text-center py-4">
                        <x-heroicon-o-credit-card class="w-12 h-12 text-gray-600 mx-auto mb-3" />
                        <p class="text-gray-400 text-sm">No active subscription</p>
                        <a href="{{ route('subscribe.plans') }}"
                           class="inline-flex items-center justify-center gap-2 mt-4 px-4 py-2.5 text-sm font-medium rounded-lg bg-white text-black hover:bg-gray-200 transition-colors">
                            View Plans
                        </a>
                    </div>
                @endif
            </div>

            <p class="text-center text-sm mt-6">
                <a href="{{ route('home') }}" class="text-gray-400 hover:text-white transition-colors">Back to home</a>
            </p>
        </div>
    </div>
</x-layouts.app>

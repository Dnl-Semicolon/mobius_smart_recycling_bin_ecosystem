<x-layouts.app title="Subscription Active">
    <div class="min-h-screen flex items-center justify-center bg-black px-4">
        <div class="w-full max-w-sm text-center">
            <div class="w-16 h-16 mx-auto rounded-full bg-emerald-900/50 flex items-center justify-center mb-4">
                <x-heroicon-o-check-circle class="w-8 h-8 text-emerald-400" />
            </div>
            <h1 class="text-xl font-semibold text-white">Subscription Active</h1>
            <p class="text-sm text-gray-400 mt-2">Your subscription has been activated successfully.</p>

            <div class="flex flex-col gap-3 mt-6">
                <a href="{{ route('subscribe.manage') }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg bg-white text-black hover:bg-gray-200 transition-colors">
                    Manage Subscription
                </a>
                <a href="{{ route('home') }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg border border-gray-700 text-gray-300 hover:bg-gray-900 transition-colors">
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>

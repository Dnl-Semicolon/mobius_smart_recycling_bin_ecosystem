<x-layouts.app title="Application Submitted">
    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
        <div class="w-full max-w-sm text-center">
            <div class="mb-6">
                <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 flex items-center justify-center mb-4">
                    <x-heroicon-o-check-circle class="w-8 h-8 text-emerald-600" />
                </div>
                <h1 class="text-xl font-semibold text-gray-900">Application Received</h1>
                <p class="text-sm text-gray-500 mt-2">
                    Your application has been submitted and is under review. You will be notified once a decision has been made.
                </p>
            </div>

            <div class="flex flex-col gap-3">
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
                    <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                    Sign In
                </a>
                <a href="{{ route('home') }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>

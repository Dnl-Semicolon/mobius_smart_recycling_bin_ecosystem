<x-layouts.app title="Access Denied — Mobius">
    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="w-full max-w-md text-center">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
                <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </div>
            <h1 class="mb-2 text-3xl font-bold text-gray-900">403</h1>
            <p class="mb-8 text-gray-600">You don't have permission to access this page.</p>
            <div class="flex items-center justify-center gap-4">
                <a href="{{ route('home') }}" class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">Go Home</a>
                <a href="{{ route('login') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Sign In</a>
            </div>
        </div>
    </div>
</x-layouts.app>

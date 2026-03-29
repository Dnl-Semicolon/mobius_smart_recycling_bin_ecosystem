<x-layouts.app title="Verify Email — Mobius">
    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
        <div class="w-full max-w-sm">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <img src="{{ asset('images/mobius-icon.png') }}" alt="Mobius" class="w-16 h-16 object-contain mx-auto mb-3">
                <img src="{{ asset('images/mobius-wordmark.png') }}" alt="Mobius" class="h-7 object-contain mx-auto mb-1">
                <p class="text-sm text-gray-500 mt-1">Smart Recycling Ecosystem</p>
            </div>

            {{-- Verification Card --}}
            <x-card class="p-6 text-center">
                <div class="w-14 h-14 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-envelope class="w-7 h-7 text-emerald-600" />
                </div>

                <h2 class="text-lg font-semibold text-gray-900 mb-2">Check your email</h2>
                <p class="text-sm text-gray-600 mb-6">
                    We've sent a verification link to <span class="font-medium text-gray-900">{{ auth()->user()->email }}</span>.
                    Click the link to verify your account.
                </p>

                @if (session('status'))
                    <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors"
                    >
                        <x-heroicon-o-arrow-path class="w-4 h-4" />
                        Resend verification email
                    </button>
                </form>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                            Sign out
                        </button>
                    </form>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>

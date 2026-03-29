<x-layouts.app title="Register — Mobius">
    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
        <div class="w-full max-w-sm">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <div class="w-14 h-14 bg-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-s-arrow-path class="w-8 h-8 text-white" />
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Mobius</h1>
                <p class="text-sm text-gray-500 mt-1">Create your account</p>
            </div>

            {{-- Register Card --}}
            <x-card class="p-6">
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <x-input
                        name="name"
                        label="Name"
                        placeholder="Your full name"
                        required
                        autofocus
                    />

                    <x-input
                        name="email"
                        type="email"
                        label="Email"
                        placeholder="you@example.com"
                        required
                    />

                    <x-password-strength :confirm="true" confirmLabel="Confirm Password" />

                    <x-button type="submit" class="w-full justify-center">
                        <x-heroicon-o-user-plus class="w-4 h-4" />
                        Create account
                    </x-button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-4">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">Sign in</a>
                </p>
            </x-card>
        </div>
    </div>
</x-layouts.app>

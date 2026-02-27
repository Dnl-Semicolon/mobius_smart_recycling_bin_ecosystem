<x-layouts.app title="Login — Mobius">
    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
        <div class="w-full max-w-sm">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <div class="w-14 h-14 bg-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-s-arrow-path class="w-8 h-8 text-white" />
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Mobius</h1>
                <p class="text-sm text-gray-500 mt-1">Smart Recycling Ecosystem</p>
            </div>

            {{-- Login Card --}}
            <x-card class="p-6">
                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <x-input
                        name="email"
                        type="email"
                        label="Email"
                        placeholder="you@example.com"
                        required
                        autofocus
                    />

                    <x-input
                        name="password"
                        type="password"
                        label="Password"
                        placeholder="Your password"
                        required
                    />

                    <label class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            name="remember"
                            class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-200"
                            @checked(old('remember'))
                        >
                        <span class="text-sm text-gray-600">Remember me</span>
                    </label>

                    <x-button type="submit" class="w-full justify-center">
                        <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                        Sign in
                    </x-button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-4">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">Register</a>
                </p>
            </x-card>
        </div>
    </div>
</x-layouts.app>

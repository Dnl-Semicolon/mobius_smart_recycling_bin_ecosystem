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

                    <div x-data="{ show: false }" class="space-y-1.5">
                        <label for="password" class="block text-sm font-medium text-gray-600">Password</label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                :type="show ? 'text' : 'password'"
                                value="{{ old('password') }}"
                                placeholder="Your password"
                                required
                                class="w-full rounded-xl border bg-white/60 px-4 py-2.5 pr-10 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 {{ $errors->has('password') ? 'border-red-300 bg-red-50/50' : 'border-gray-200/80' }}"
                            >
                            <button
                                type="button"
                                @click="show = !show"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                tabindex="-1"
                            >
                                <x-heroicon-o-eye x-show="!show" class="w-4.5 h-4.5" />
                                <x-heroicon-o-eye-slash x-show="show" class="w-4.5 h-4.5" x-cloak />
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

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

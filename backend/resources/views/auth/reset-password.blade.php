<x-layouts.app title="Reset Password — Mobius">
    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 relative overflow-hidden">
        <div class="hero-orb w-[400px] h-[400px] bg-emerald-500/10 -top-32 -right-20" style="animation-delay: 0s;"></div>
        <div class="hero-orb w-[300px] h-[300px] bg-teal-400/8 -bottom-20 -left-24" style="animation-delay: -7s;"></div>

        <div class="w-full max-w-sm relative z-10">
            <div class="text-center mb-8 hero-animate">
                <img src="{{ asset('images/mobius-icon.png') }}" alt="Mobius" class="w-16 h-16 object-contain mx-auto mb-3">
                <img src="{{ asset('images/mobius-wordmark.png') }}" alt="Mobius" class="h-7 object-contain mx-auto">
                <h1 class="text-xl font-semibold text-gray-900 mt-3">Set new password</h1>
                <p class="text-sm text-gray-500 mt-1">Choose a strong password for your account.</p>
            </div>

            <x-card class="p-6 hero-animate hero-animate-delay-1">
                <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    {{-- Show which email is being reset --}}
                    <div class="flex items-center gap-2 rounded-lg bg-gray-50 border border-gray-200/60 px-3 py-2">
                        <x-heroicon-o-envelope class="w-4 h-4 text-gray-400 shrink-0" />
                        <span class="text-sm text-gray-600 truncate">{{ $email }}</span>
                    </div>

                    <div x-data="{ show: false }" class="space-y-1.5">
                        <label for="password" class="block text-sm font-medium text-gray-600">New password</label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                :type="show ? 'text' : 'password'"
                                placeholder="Minimum 8 characters"
                                required
                                autofocus
                                class="w-full rounded-xl border bg-white/60 px-4 py-2.5 pr-10 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 {{ $errors->has('password') ? 'border-red-300 bg-red-50/50' : 'border-gray-200/80' }}"
                            >
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" tabindex="-1">
                                <x-heroicon-o-eye x-show="!show" class="w-4.5 h-4.5" />
                                <x-heroicon-o-eye-slash x-show="show" class="w-4.5 h-4.5" x-cloak />
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-data="{ show: false }" class="space-y-1.5">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-600">Confirm new password</label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                :type="show ? 'text' : 'password'"
                                placeholder="Repeat your password"
                                required
                                class="w-full rounded-xl border border-gray-200/80 bg-white/60 px-4 py-2.5 pr-10 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10"
                            >
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" tabindex="-1">
                                <x-heroicon-o-eye x-show="!show" class="w-4.5 h-4.5" />
                                <x-heroicon-o-eye-slash x-show="show" class="w-4.5 h-4.5" x-cloak />
                            </button>
                        </div>
                    </div>

                    <x-button type="submit" class="w-full justify-center cursor-pointer py-3 px-6 transition-all duration-150 active:scale-[0.98] shadow-sm hover:shadow-md">
                        <x-heroicon-o-lock-closed class="w-4 h-4" />
                        Reset password
                    </x-button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-4">
                    <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-700 font-medium underline decoration-emerald-300 underline-offset-2 hover:decoration-emerald-500">Back to sign in</a>
                </p>
            </x-card>
        </div>
    </div>
</x-layouts.app>

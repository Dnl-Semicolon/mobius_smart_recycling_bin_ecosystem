<x-layouts.app title="Login — Mobius">
    {{-- Toast Notifications --}}
    <div
        x-data="{
            toasts: [],
            add(type, message) {
                var id = Date.now();
                this.toasts.push({ id, type, message });
                setTimeout(() => this.remove(id), 4000);
            },
            remove(id) {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }
        }"
        x-init="
            @if (session('success')) add('success', @js(session('success'))); @endif
            @if (session('error')) add('error', @js(session('error'))); @endif
        "
        class="fixed top-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none"
        data-testid="toast-container"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="true"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 translate-x-4"
                class="pointer-events-auto flex items-center gap-2.5 rounded-lg border px-4 py-3 text-sm font-medium shadow-lg shadow-black/8 backdrop-blur-sm min-w-[280px] max-w-[420px]"
                :class="toast.type === 'success'
                    ? 'bg-white/95 border-emerald-200 text-emerald-700'
                    : 'bg-white/95 border-red-200 text-red-700'"
            >
                <template x-if="toast.type === 'success'">
                    <svg class="w-5 h-5 shrink-0 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="w-5 h-5 shrink-0 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
                </template>
                <span x-text="toast.message" class="flex-1"></span>
                <button @click="remove(toast.id)" class="shrink-0 rounded p-0.5 hover:bg-black/5 transition-colors cursor-pointer">
                    <svg class="w-4 h-4 opacity-40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" /></svg>
                </button>
            </div>
        </template>
    </div>

    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 relative overflow-hidden">
        {{-- Atmospheric background orbs --}}
        <div class="hero-orb w-[400px] h-[400px] bg-emerald-500/10 -top-32 -right-20" style="animation-delay: 0s;"></div>
        <div class="hero-orb w-[300px] h-[300px] bg-teal-400/8 -bottom-20 -left-24" style="animation-delay: -7s;"></div>

        <div class="w-full max-w-sm relative z-10">
            {{-- Logo --}}
            <div class="text-center mb-8 hero-animate">
                <img src="{{ asset('images/mobius-icon.png') }}" alt="Mobius" class="w-16 h-16 object-contain mx-auto mb-3">
                <img src="{{ asset('images/mobius-wordmark.png') }}" alt="Mobius" class="h-7 object-contain mx-auto">
                <h1 class="text-xl font-semibold text-gray-900 mt-3">Sign in to Mobius</h1>
            </div>

            {{-- Login Card --}}
            <x-card class="p-6 hero-animate hero-animate-delay-1">
                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div x-data="{
                        email: @js(old('email', '')),
                        get isValid() { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email) },
                    }" class="space-y-1.5">
                        <label for="email" class="block text-sm font-medium text-gray-600">Email</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            x-model="email"
                            placeholder="you@example.com"
                            required
                            autofocus
                            class="w-full rounded-xl border bg-white/60 px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 {{ $errors->has('email') ? 'border-red-300 bg-red-50/50' : 'border-gray-200/80' }}"
                        >
                        <div x-show="email.length > 0" x-cloak class="flex items-center gap-1.5">
                            <svg x-show="isValid" class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span x-show="isValid" class="text-xs text-emerald-600">Valid email format</span>
                            <svg x-show="!isValid" class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span x-show="!isValid" class="text-xs text-gray-400">Enter a valid email address</span>
                        </div>
                        @error('email')
                            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

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

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                name="remember"
                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-200"
                                @checked(old('remember'))
                            >
                            <span class="text-sm text-gray-600">Remember me</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">Forgot password?</a>
                    </div>

                    <x-button type="submit" class="w-full justify-center cursor-pointer py-3 px-6 transition-all duration-150 active:scale-[0.98] shadow-sm hover:shadow-md">
                        <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                        Sign in
                    </x-button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-4">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="text-emerald-600 hover:text-emerald-700 font-medium underline decoration-emerald-300 underline-offset-2 hover:decoration-emerald-500">Register</a>
                </p>
            </x-card>
        </div>
    </div>
</x-layouts.app>

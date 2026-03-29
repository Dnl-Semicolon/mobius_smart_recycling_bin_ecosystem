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

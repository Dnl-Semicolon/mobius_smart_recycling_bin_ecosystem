<x-layouts.app title="Forgot Password — Mobius">
    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 relative overflow-hidden">
        <div class="hero-orb w-[400px] h-[400px] bg-emerald-500/10 -top-32 -right-20" style="animation-delay: 0s;"></div>
        <div class="hero-orb w-[300px] h-[300px] bg-teal-400/8 -bottom-20 -left-24" style="animation-delay: -7s;"></div>

        <div class="w-full max-w-sm relative z-10">
            <div class="text-center mb-8 hero-animate">
                <img src="{{ asset('images/mobius-icon.png') }}" alt="Mobius" class="w-16 h-16 object-contain mx-auto mb-3">
                <img src="{{ asset('images/mobius-wordmark.png') }}" alt="Mobius" class="h-7 object-contain mx-auto">
                <h1 class="text-xl font-semibold text-gray-900 mt-3">Reset your password</h1>
                <p class="text-sm text-gray-500 mt-1">Enter your email and we'll send you a reset link.</p>
            </div>

            <x-card class="p-6 hero-animate hero-animate-delay-1">
                @if (session('status'))
                    <div class="mb-4 flex items-center gap-2.5 rounded-lg border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-700">
                        <svg class="w-5 h-5 shrink-0 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
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

                    <x-button type="submit" class="w-full justify-center cursor-pointer py-3 px-6 transition-all duration-150 active:scale-[0.98] shadow-sm hover:shadow-md">
                        <x-heroicon-o-envelope class="w-4 h-4" />
                        Send reset link
                    </x-button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-4">
                    <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-700 font-medium underline decoration-emerald-300 underline-offset-2 hover:decoration-emerald-500">Back to sign in</a>
                </p>
            </x-card>
        </div>
    </div>
</x-layouts.app>

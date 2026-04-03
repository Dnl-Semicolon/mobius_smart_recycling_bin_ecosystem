<x-layouts.app title="Verify Phone — Mobius">
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 via-white to-emerald-50/30 px-4 py-12">
        <div class="w-full max-w-md">

            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Verify Your Phone</h1>
                <p class="text-gray-500 mt-2">We sent a 6-digit code to your phone. Enter it below to complete registration.</p>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3">
                    <p class="text-sm text-emerald-700">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <form method="POST" action="{{ route('registration.confirm-phone') }}">
                    @csrf

                    <div class="mb-6">
                        <label for="otp" class="block text-sm font-medium text-gray-700 mb-2">Verification Code</label>
                        <input
                            type="text"
                            id="otp"
                            name="otp"
                            maxlength="6"
                            placeholder="000000"
                            required
                            autofocus
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-4 text-2xl text-gray-900 placeholder-gray-300 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/10 focus:outline-none transition-all font-mono tracking-[0.5em] text-center"
                        >
                        @error('otp')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors shadow-sm">
                        Verify & Submit Application
                    </button>
                </form>

                <p class="text-center text-xs text-gray-400 mt-4">
                    Didn't receive the code? <a href="{{ route('registration.brand.create') }}" class="text-emerald-600 hover:underline">Go back and try again</a>
                </p>
            </div>

        </div>
    </div>
</x-layouts.app>

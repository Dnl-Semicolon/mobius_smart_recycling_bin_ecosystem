<x-layouts.app title="Registration — Mobius">
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 via-white to-emerald-50/30 px-4">
        <div class="w-full max-w-md text-center">

            @if (session('error'))
                <div class="mb-6 rounded-xl bg-red-50 border border-red-200 px-5 py-4 text-left">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center shrink-0 mt-0.5">
                            <x-heroicon-s-x-circle class="w-5 h-5 text-red-500" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-red-800">Something went wrong</p>
                            <p class="text-sm text-red-600 mt-0.5">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                @if (session('success') && str_contains(session('success'), 'Email verified'))
                    {{-- Email just verified --}}
                    <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 flex items-center justify-center mb-5">
                        <x-heroicon-s-check-badge class="w-9 h-9 text-emerald-600" />
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Email Verified!</h1>
                    <p class="text-gray-500 mt-3 text-sm leading-relaxed">
                        Your email has been confirmed. Your application is now visible to our team and under review.
                        We'll contact you within <strong>2 business days</strong>.
                    </p>
                    <div class="mt-5 flex items-center justify-center gap-2 text-xs text-emerald-600 font-medium">
                        <x-heroicon-s-check-circle class="w-4 h-4" /> Phone verified
                        <span class="text-gray-300 mx-1">|</span>
                        <x-heroicon-s-check-circle class="w-4 h-4" /> Email verified
                    </div>

                @elseif (session('success') && str_contains(session('success'), 'Check your inbox'))
                    {{-- Phone verified, email pending --}}
                    <div class="w-16 h-16 mx-auto rounded-full bg-blue-100 flex items-center justify-center mb-5">
                        <x-heroicon-s-envelope class="w-9 h-9 text-blue-600" />
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Check Your Inbox</h1>
                    <p class="text-gray-500 mt-3 text-sm leading-relaxed">
                        Your phone has been verified. We've sent a verification link to your email.
                        <strong>Click the link to complete your registration.</strong>
                    </p>
                    <div class="mt-5 flex items-center justify-center gap-2 text-xs font-medium">
                        <span class="text-emerald-600 flex items-center gap-1"><x-heroicon-s-check-circle class="w-4 h-4" /> Phone verified</span>
                        <span class="text-gray-300 mx-1">|</span>
                        <span class="text-amber-500 flex items-center gap-1"><x-heroicon-s-clock class="w-4 h-4" /> Email pending</span>
                    </div>
                    <div class="mt-5 rounded-lg bg-gray-50 border border-gray-200 px-4 py-3">
                        <p class="text-xs text-gray-500">Didn't receive it? Check your spam folder or <a href="{{ route('registration.brand.create') }}" class="text-emerald-600 hover:underline">register again</a>.</p>
                    </div>

                @else
                    {{-- Generic success (e.g. from landing page form) --}}
                    <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 flex items-center justify-center mb-5">
                        <x-heroicon-s-paper-airplane class="w-9 h-9 text-emerald-600" />
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Application Received</h1>
                    <p class="text-gray-500 mt-3 text-sm leading-relaxed">
                        {{ session('success', 'Your application has been submitted successfully.') }}
                        Our team will review it and get back to you within <strong>2 business days</strong>.
                    </p>
                @endif

                <div class="mt-8 flex flex-col gap-3">
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
                        Back to Home
                    </a>
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                        Already have an account? Sign In
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>

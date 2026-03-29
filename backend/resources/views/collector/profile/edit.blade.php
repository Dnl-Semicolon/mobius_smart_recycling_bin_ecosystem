<x-layouts.app title="My Profile — Mobius">
    <div class="min-h-screen bg-gray-50">
        {{-- Header --}}
        <header class="bg-white border-b border-gray-200/60">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('collector.dashboard') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/mobius-icon.png') }}" alt="Mobius" class="w-8 h-8 object-contain">
                        <img src="{{ asset('images/mobius-wordmark.png') }}" alt="Mobius" class="h-5 object-contain">
                    </a>
                    <span class="text-xs text-gray-400 border-l border-gray-200 pl-3 ml-1">Collector</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('collector.dashboard') }}" class="text-sm text-gray-400 hover:text-gray-600 transition-colors">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-400 hover:text-red-600 transition-colors">Sign out</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="max-w-2xl mx-auto px-4 sm:px-6 py-8">
            <h1 class="text-lg font-semibold text-gray-900 mb-6">My Profile</h1>

            @include('partials.profile-form', [
                'user' => $user,
                'updateRoute' => route('collector.profile.update'),
                'passwordRoute' => route('collector.profile.password'),
            ])
        </main>
    </div>
</x-layouts.app>

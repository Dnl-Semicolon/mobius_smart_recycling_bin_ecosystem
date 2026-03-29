<x-layouts.app title="Plans & Pricing">
    <div class="min-h-screen flex items-center justify-center bg-black px-4 py-16">
        <div class="w-full max-w-2xl">
            <h1 class="text-2xl font-semibold text-white text-center mb-8">Plans & Pricing</h1>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Basic --}}
                <div class="rounded-lg border border-gray-800 bg-gray-950 p-6">
                    <h2 class="text-white font-medium text-lg">Basic</h2>
                    <p class="text-3xl font-bold text-white mt-2">RM49<span class="text-sm font-normal text-gray-500">/mo</span></p>
                    <ul class="mt-4 space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <x-heroicon-s-check class="w-4 h-4 text-emerald-500" />
                            Up to 5 outlets
                        </li>
                        <li class="flex items-center gap-2">
                            <x-heroicon-s-check class="w-4 h-4 text-emerald-500" />
                            Basic analytics
                        </li>
                        <li class="flex items-center gap-2">
                            <x-heroicon-s-check class="w-4 h-4 text-emerald-500" />
                            Standard support
                        </li>
                    </ul>
                    @auth
                        <form method="POST" action="{{ route('subscribe.checkout') }}">
                            @csrf
                            <input type="hidden" name="price" value="basic">
                            <button type="submit" class="mt-6 w-full rounded-lg bg-white text-black font-medium py-2.5 text-sm hover:bg-gray-200 transition-colors cursor-pointer">
                                Subscribe
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="mt-6 block w-full rounded-lg bg-white text-black font-medium py-2.5 text-sm text-center hover:bg-gray-200 transition-colors">
                            Sign in to subscribe
                        </a>
                    @endauth
                </div>

                {{-- Premium --}}
                <div class="rounded-lg border border-emerald-800 bg-gray-950 p-6 ring-1 ring-emerald-500/20">
                    <h2 class="text-white font-medium text-lg">Premium</h2>
                    <p class="text-3xl font-bold text-white mt-2">RM149<span class="text-sm font-normal text-gray-500">/mo</span></p>
                    <ul class="mt-4 space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <x-heroicon-s-check class="w-4 h-4 text-emerald-500" />
                            Unlimited outlets
                        </li>
                        <li class="flex items-center gap-2">
                            <x-heroicon-s-check class="w-4 h-4 text-emerald-500" />
                            Advanced analytics & reports
                        </li>
                        <li class="flex items-center gap-2">
                            <x-heroicon-s-check class="w-4 h-4 text-emerald-500" />
                            Priority support
                        </li>
                        <li class="flex items-center gap-2">
                            <x-heroicon-s-check class="w-4 h-4 text-emerald-500" />
                            Custom brand multiplier
                        </li>
                    </ul>
                    @auth
                        <form method="POST" action="{{ route('subscribe.checkout') }}">
                            @csrf
                            <input type="hidden" name="price" value="premium">
                            <button type="submit" class="mt-6 w-full rounded-lg bg-emerald-600 text-white font-medium py-2.5 text-sm hover:bg-emerald-700 transition-colors cursor-pointer">
                                Subscribe
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="mt-6 block w-full rounded-lg bg-emerald-600 text-white font-medium py-2.5 text-sm text-center hover:bg-emerald-700 transition-colors">
                            Sign in to subscribe
                        </a>
                    @endauth
                </div>
            </div>

            <p class="text-center text-sm text-gray-600 mt-8">
                <a href="{{ route('home') }}" class="text-gray-400 hover:text-white transition-colors">Back to home</a>
            </p>
        </div>
    </div>
</x-layouts.app>

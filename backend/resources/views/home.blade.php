<x-layouts.app title="Mobius — Smart Recycling for Beverage Brands">

<style>
    .gradient-text {
        background: linear-gradient(135deg, #059669, #10b981, #34d399);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>

{{-- ═══════════════════════════════════════════════════════════════════
     STICKY NAVIGATION
     ═══════════════════════════════════════════════════════════════════ --}}
<nav x-data="{ open: false, scrolled: false }"
     @scroll.window="scrolled = window.scrollY > 50"
     :class="scrolled ? 'bg-white/90 backdrop-blur-xl shadow-sm border-b border-gray-200/60' : 'bg-white/60 backdrop-blur-md'"
     class="fixed top-0 inset-x-0 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <span class="text-xl font-bold text-gray-900">Mobius</span>
        </a>

        {{-- Desktop links --}}
        <div class="hidden md:flex items-center gap-8">
            <a href="#how-it-works" class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition-colors">How It Works</a>
            <a href="#features" class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition-colors">Features</a>
            <a href="#pricing" class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition-colors">Pricing</a>
        </div>

        {{-- Desktop CTAs --}}
        <div class="hidden md:flex items-center gap-3">
            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Sign In</a>
            <a href="{{ route('registration.brand.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition-colors shadow-sm">
                Request Demo
            </a>
        </div>

        {{-- Mobile hamburger --}}
        <button @click="open = !open" class="md:hidden p-2 -mr-2 text-gray-700" aria-label="Toggle menu">
            <svg x-show="!open" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 9h16.5m-16.5 6.75h16.5"/>
            </svg>
            <svg x-show="open" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Mobile menu --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="md:hidden bg-white border-b border-gray-200 shadow-lg">
        <div class="px-6 py-4 space-y-1">
            <a href="#how-it-works" @click="open = false" class="block py-2.5 text-sm font-medium text-gray-700 hover:text-emerald-600">How It Works</a>
            <a href="#features" @click="open = false" class="block py-2.5 text-sm font-medium text-gray-700 hover:text-emerald-600">Features</a>
            <a href="#pricing" @click="open = false" class="block py-2.5 text-sm font-medium text-gray-700 hover:text-emerald-600">Pricing</a>
            <div class="pt-3 border-t border-gray-100 flex flex-col gap-2">
                <a href="{{ route('login') }}" class="block py-2.5 text-sm font-medium text-gray-700">Sign In</a>
                <a href="{{ route('registration.brand.create') }}" class="block py-2.5 px-4 text-center rounded-lg bg-emerald-600 text-white text-sm font-semibold">Request Demo</a>
            </div>
        </div>
    </div>
</nav>

{{-- ═══════════════════════════════════════════════════════════════════
     HERO SECTION
     ═══════════════════════════════════════════════════════════════════ --}}
<section class="relative pt-32 pb-20 lg:pt-40 lg:pb-28 bg-gradient-to-br from-green-50 via-white to-emerald-50 overflow-hidden">
    {{-- Decorative blobs --}}
    <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-100/50 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-green-100/40 rounded-full blur-3xl translate-y-1/3 -translate-x-1/4"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6">
        <div class="text-center max-w-4xl mx-auto">
            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-emerald-200 bg-emerald-50 mb-8">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-emerald-700 text-xs font-semibold tracking-wide">Now live in Penang, expanding to KL</span>
            </div>

            {{-- Headline --}}
            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold text-gray-900 tracking-tight leading-[1.1]">
                Smarter Cups.<br>
                <span class="gradient-text">Greener Cities.</span>
            </h1>

            {{-- Subtitle --}}
            <p class="mt-6 text-lg lg:text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">
                Mobius brings AI-powered smart bins to your beverage outlets. Turn every cup disposal into a loyalty moment with computer vision, real-time analytics, and gamified rewards.
            </p>

            {{-- CTAs --}}
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('registration.brand.create') }}"
                   class="group w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20">
                    Request a Demo
                    <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
                <a href="#how-it-works"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:border-gray-400 hover:bg-gray-50 transition-all">
                    See How It Works
                </a>
            </div>
        </div>

        {{-- Stats --}}
        <div class="mt-20 grid grid-cols-2 lg:grid-cols-4 gap-8 max-w-4xl mx-auto">
            <div class="text-center">
                <div class="text-3xl lg:text-4xl font-extrabold text-gray-900">250,000+</div>
                <div class="mt-1 text-sm text-gray-500 font-medium">Cups Recycled</div>
            </div>
            <div class="text-center">
                <div class="text-3xl lg:text-4xl font-extrabold text-gray-900">48</div>
                <div class="mt-1 text-sm text-gray-500 font-medium">Active Smart Bins</div>
            </div>
            <div class="text-center">
                <div class="text-3xl lg:text-4xl font-extrabold text-gray-900">3+</div>
                <div class="mt-1 text-sm text-gray-500 font-medium">Partner Brands</div>
            </div>
            <div class="text-center">
                <div class="text-3xl lg:text-4xl font-extrabold text-gray-900">12,000+</div>
                <div class="mt-1 text-sm text-gray-500 font-medium">Points Awarded</div>
            </div>
        </div>

        {{-- Dashboard preview mockup --}}
        <div class="mt-20 max-w-5xl mx-auto">
            <div class="bg-white rounded-2xl shadow-2xl shadow-gray-200/60 border border-gray-200/80 overflow-hidden">
                {{-- Mockup browser bar --}}
                <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 border-b border-gray-200/80">
                    <div class="flex gap-1.5">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                    </div>
                    <div class="flex-1 flex justify-center">
                        <div class="px-4 py-1 bg-white rounded-md text-xs text-gray-400 border border-gray-200 font-mono">dashboard.mobius.my</div>
                    </div>
                </div>
                {{-- Dashboard content --}}
                <div class="p-6 lg:p-8">
                    {{-- Top metric cards --}}
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100">
                            <div class="text-xs font-medium text-emerald-600 uppercase tracking-wide">Active Bins</div>
                            <div class="mt-2 text-2xl font-bold text-gray-900">48</div>
                            <div class="mt-1 text-xs text-emerald-600">+3 this week</div>
                        </div>
                        <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                            <div class="text-xs font-medium text-blue-600 uppercase tracking-wide">Avg Fill Level</div>
                            <div class="mt-2 text-2xl font-bold text-gray-900">67%</div>
                            <div class="mt-1 text-xs text-blue-600">Optimal range</div>
                        </div>
                        <div class="bg-amber-50 rounded-xl p-4 border border-amber-100">
                            <div class="text-xs font-medium text-amber-600 uppercase tracking-wide">Today's Sessions</div>
                            <div class="mt-2 text-2xl font-bold text-gray-900">1,284</div>
                            <div class="mt-1 text-xs text-amber-600">+12% vs yesterday</div>
                        </div>
                        <div class="bg-purple-50 rounded-xl p-4 border border-purple-100">
                            <div class="text-xs font-medium text-purple-600 uppercase tracking-wide">Points Given</div>
                            <div class="mt-2 text-2xl font-bold text-gray-900">8,420</div>
                            <div class="mt-1 text-xs text-purple-600">Across 3 brands</div>
                        </div>
                    </div>
                    {{-- Bottom section --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="lg:col-span-2 bg-gray-50 rounded-xl p-5 border border-gray-100">
                            <div class="text-sm font-semibold text-gray-700 mb-4">Weekly Recycling Volume</div>
                            <div class="flex items-end gap-3 h-32">
                                <div class="flex-1 bg-emerald-200 rounded-t-md" style="height: 45%"></div>
                                <div class="flex-1 bg-emerald-300 rounded-t-md" style="height: 60%"></div>
                                <div class="flex-1 bg-emerald-400 rounded-t-md" style="height: 75%"></div>
                                <div class="flex-1 bg-emerald-300 rounded-t-md" style="height: 55%"></div>
                                <div class="flex-1 bg-emerald-500 rounded-t-md" style="height: 90%"></div>
                                <div class="flex-1 bg-emerald-400 rounded-t-md" style="height: 80%"></div>
                                <div class="flex-1 bg-emerald-200 rounded-t-md" style="height: 35%"></div>
                            </div>
                            <div class="flex gap-3 mt-2">
                                <div class="flex-1 text-center text-xs text-gray-400">Mon</div>
                                <div class="flex-1 text-center text-xs text-gray-400">Tue</div>
                                <div class="flex-1 text-center text-xs text-gray-400">Wed</div>
                                <div class="flex-1 text-center text-xs text-gray-400">Thu</div>
                                <div class="flex-1 text-center text-xs text-gray-400">Fri</div>
                                <div class="flex-1 text-center text-xs text-gray-400">Sat</div>
                                <div class="flex-1 text-center text-xs text-gray-400">Sun</div>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                            <div class="text-sm font-semibold text-gray-700 mb-4">Top Locations</div>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Gurney Plaza</span>
                                    <span class="text-sm font-semibold text-gray-900">342</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5"><div class="bg-emerald-500 h-1.5 rounded-full" style="width: 85%"></div></div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Queensbay Mall</span>
                                    <span class="text-sm font-semibold text-gray-900">281</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5"><div class="bg-emerald-500 h-1.5 rounded-full" style="width: 70%"></div></div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Komtar</span>
                                    <span class="text-sm font-semibold text-gray-900">195</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5"><div class="bg-emerald-500 h-1.5 rounded-full" style="width: 48%"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     HOW IT WORKS
     ═══════════════════════════════════════════════════════════════════ --}}
<section id="how-it-works" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-6">
        {{-- Header --}}
        <div class="text-center max-w-2xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-semibold tracking-wide mb-4">
                HOW IT WORKS
            </div>
            <h2 class="text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight">
                From bin to rewards in 4 steps
            </h2>
            <p class="mt-4 text-gray-600 text-lg">
                A seamless loop connecting your brand, your customers, and the planet.
            </p>
        </div>

        {{-- Steps --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {{-- Step 1 --}}
            <div class="relative bg-white rounded-2xl border border-gray-200 p-6 hover:shadow-lg hover:border-emerald-200 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                    </svg>
                </div>
                <div class="absolute top-4 right-4 text-4xl font-extrabold text-gray-100">01</div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Deploy Smart Bins</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    We install branded smart bins with built-in cameras and IoT sensors at your outlet locations. Each bin is configured for your brand within 24 hours.
                </p>
            </div>

            {{-- Step 2 --}}
            <div class="relative bg-white rounded-2xl border border-gray-200 p-6 hover:shadow-lg hover:border-emerald-200 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75H16.5v-.75z"/>
                    </svg>
                </div>
                <div class="absolute top-4 right-4 text-4xl font-extrabold text-gray-100">02</div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Customer Scans & Recycles</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Customers scan a QR code on the bin with their phone, then drop in their used cup. The process takes under 10 seconds with no app download required.
                </p>
            </div>

            {{-- Step 3 --}}
            <div class="relative bg-white rounded-2xl border border-gray-200 p-6 hover:shadow-lg hover:border-emerald-200 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/>
                    </svg>
                </div>
                <div class="absolute top-4 right-4 text-4xl font-extrabold text-gray-100">03</div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">AI Detects & Awards Points</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Our YOLOv8 vision model identifies the waste type and cup brand in real-time. Points are calculated instantly with brand loyalty bonuses.
                </p>
            </div>

            {{-- Step 4 --}}
            <div class="relative bg-white rounded-2xl border border-gray-200 p-6 hover:shadow-lg hover:border-emerald-200 transition-all duration-300">
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                    </svg>
                </div>
                <div class="absolute top-4 right-4 text-4xl font-extrabold text-gray-100">04</div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Redeem Rewards</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Customers redeem points for vouchers at partner outlets. Brands gain loyal customers who return, recycle, and repeat. A virtuous loop.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     FEATURES
     ═══════════════════════════════════════════════════════════════════ --}}
<section id="features" class="py-24 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-7xl mx-auto px-6">
        {{-- Header --}}
        <div class="text-center max-w-2xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-semibold tracking-wide mb-4">
                FEATURES
            </div>
            <h2 class="text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight">
                Everything your brand needs
            </h2>
            <p class="mt-4 text-gray-600 text-lg">
                From AI-powered waste detection to gamified loyalty, we handle the full recycling stack.
            </p>
        </div>

        {{-- Feature cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {{-- YOLOv8 Vision AI --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 hover:shadow-lg hover:border-emerald-200 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center mb-5 group-hover:bg-emerald-200 transition-colors">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">YOLOv8 Vision AI</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Real-time waste classification using custom-trained YOLOv8 models. Detects cups, lids, straws, and liquid waste with 95%+ accuracy. Identifies cup brands for loyalty scoring.
                </p>
            </div>

            {{-- Smart IoT Hardware --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 hover:shadow-lg hover:border-emerald-200 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mb-5 group-hover:bg-blue-200 transition-colors">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Smart IoT Hardware</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Ultrasonic fill-level sensors, weight detectors, and embedded cameras. Each bin reports capacity in real-time and auto-triggers collection routes when full.
                </p>
            </div>

            {{-- Points & Streaks --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 hover:shadow-lg hover:border-emerald-200 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center mb-5 group-hover:bg-amber-200 transition-colors">
                    <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 5.974 5.974 0 01-2.133-1A3.75 3.75 0 0012 18z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Points & Streaks</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Gamified recycling with daily streaks, bonus multipliers, and brand loyalty rewards. Customers earn more for consistent recycling and same-brand cup returns.
                </p>
            </div>

            {{-- Voucher Campaigns --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 hover:shadow-lg hover:border-emerald-200 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-rose-100 flex items-center justify-center mb-5 group-hover:bg-rose-200 transition-colors">
                    <svg class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Voucher Campaigns</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Create and manage targeted voucher campaigns. Set custom redemption thresholds, expiry dates, and outlet restrictions. Track ROI in real-time.
                </p>
            </div>

            {{-- Analytics Dashboard --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 hover:shadow-lg hover:border-emerald-200 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center mb-5 group-hover:bg-indigo-200 transition-colors">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Analytics Dashboard</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Real-time insights into recycling volumes, peak hours, brand loyalty metrics, and environmental impact. Export reports and integrate with your BI tools.
                </p>
            </div>

            {{-- Multi-role Management --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 hover:shadow-lg hover:border-emerald-200 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-teal-100 flex items-center justify-center mb-5 group-hover:bg-teal-200 transition-colors">
                    <svg class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Multi-role Management</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Separate dashboards for brand owners, store managers, waste collectors, and admins. Role-based access controls with granular permissions for every function.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     SOCIAL PROOF
     ═══════════════════════════════════════════════════════════════════ --}}
<section class="py-16 bg-white border-y border-gray-100">
    <div class="max-w-7xl mx-auto px-6">
        <p class="text-center text-sm font-semibold text-gray-400 uppercase tracking-widest mb-10">
            Trusted by leading Malaysian brands
        </p>
        <div class="flex flex-wrap items-center justify-center gap-x-16 gap-y-8">
            <div class="text-2xl font-bold text-gray-300 tracking-tight">Starbucks</div>
            <div class="text-2xl font-bold text-gray-300 tracking-tight">Mixue</div>
            <div class="text-2xl font-bold text-gray-300 tracking-tight">Tealive</div>
            <div class="text-2xl font-bold text-gray-300 tracking-tight">ZUS Coffee</div>
            <div class="text-2xl font-bold text-gray-300 tracking-tight">Gong Cha</div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     PRICING
     ═══════════════════════════════════════════════════════════════════ --}}
<section id="pricing" class="py-24 bg-gradient-to-b from-white to-green-50/50"
         x-data="{ yearly: false }">
    <div class="max-w-7xl mx-auto px-6">
        {{-- Header --}}
        <div class="text-center max-w-2xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-semibold tracking-wide mb-4">
                PRICING
            </div>
            <h2 class="text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight">
                Simple, transparent pricing
            </h2>
            <p class="mt-4 text-gray-600 text-lg">
                Start small, scale fast. All plans include setup, training, and ongoing support.
            </p>

            {{-- Toggle --}}
            <div class="mt-8 inline-flex items-center gap-3 bg-gray-100 rounded-full p-1">
                <button @click="yearly = false"
                        :class="!yearly ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'"
                        class="px-5 py-2 rounded-full text-sm font-semibold transition-all">
                    Monthly
                </button>
                <button @click="yearly = true"
                        :class="yearly ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'"
                        class="px-5 py-2 rounded-full text-sm font-semibold transition-all">
                    Yearly <span class="text-emerald-600 text-xs font-bold ml-1">Save 20%</span>
                </button>
            </div>
        </div>

        {{-- Plan cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            {{-- Basic --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-8 flex flex-col">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Basic</h3>
                    <p class="text-sm text-gray-500 mt-1">For small outlets getting started</p>
                </div>
                <div class="mb-6">
                    <div class="flex items-baseline gap-1">
                        <span class="text-4xl font-extrabold text-gray-900" x-text="yearly ? 'RM239' : 'RM299'">RM299</span>
                        <span class="text-sm text-gray-500">/month</span>
                    </div>
                    <p x-show="yearly" x-cloak class="text-xs text-emerald-600 font-medium mt-1">Billed annually (RM2,868/yr)</p>
                </div>
                <ul class="space-y-3 mb-8 flex-1">
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Up to 2 smart bins
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Basic analytics dashboard
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        AI waste detection
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Customer rewards system
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Email support
                    </li>
                </ul>
                <a href="{{ route('registration.brand.create') }}"
                   class="block w-full text-center px-6 py-3 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                    Get Started
                </a>
            </div>

            {{-- Pro (highlighted) --}}
            <div class="bg-white rounded-2xl border-2 border-emerald-500 p-8 flex flex-col relative shadow-lg shadow-emerald-100">
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                    <span class="px-4 py-1 bg-emerald-600 text-white text-xs font-bold rounded-full uppercase tracking-wide">Most Popular</span>
                </div>
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Pro</h3>
                    <p class="text-sm text-gray-500 mt-1">For growing chains and franchises</p>
                </div>
                <div class="mb-6">
                    <div class="flex items-baseline gap-1">
                        <span class="text-4xl font-extrabold text-gray-900" x-text="yearly ? 'RM479' : 'RM599'">RM599</span>
                        <span class="text-sm text-gray-500">/month</span>
                    </div>
                    <p x-show="yearly" x-cloak class="text-xs text-emerald-600 font-medium mt-1">Billed annually (RM5,748/yr)</p>
                </div>
                <ul class="space-y-3 mb-8 flex-1">
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Up to 10 smart bins
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Advanced analytics & reports
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Brand loyalty detection
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Voucher campaign manager
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Route optimization
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Priority support
                    </li>
                </ul>
                <a href="{{ route('registration.brand.create') }}"
                   class="block w-full text-center px-6 py-3 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition-colors shadow-sm">
                    Get Started
                </a>
            </div>

            {{-- Enterprise --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-8 flex flex-col">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Enterprise</h3>
                    <p class="text-sm text-gray-500 mt-1">For large-scale deployments</p>
                </div>
                <div class="mb-6">
                    <div class="flex items-baseline gap-1">
                        <span class="text-4xl font-extrabold text-gray-900" x-text="yearly ? 'RM799' : 'RM999'">RM999</span>
                        <span class="text-sm text-gray-500">/month</span>
                    </div>
                    <p x-show="yearly" x-cloak class="text-xs text-emerald-600 font-medium mt-1">Billed annually (RM9,588/yr)</p>
                </div>
                <ul class="space-y-3 mb-8 flex-1">
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Unlimited smart bins
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        White-label branding
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Custom AI model training
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        API access & integrations
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Multi-region deployment
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        Dedicated account manager
                    </li>
                </ul>
                <a href="{{ route('registration.brand.create') }}"
                   class="block w-full text-center px-6 py-3 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                    Get Started
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     TESTIMONIALS
     ═══════════════════════════════════════════════════════════════════ --}}
<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-6">
        {{-- Header --}}
        <div class="text-center max-w-2xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-semibold tracking-wide mb-4">
                TESTIMONIALS
            </div>
            <h2 class="text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight">
                What our partners say
            </h2>
        </div>

        {{-- Testimonial cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            {{-- Sarah Lee --}}
            <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                <div class="flex items-center gap-1 mb-4">
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed mb-6">
                    "Mobius transformed our outlets' recycling compliance. We've seen a 3x increase in cup returns and our customers love the points system. The brand loyalty detection is genius."
                </p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-sm">SL</div>
                    <div>
                        <div class="text-sm font-semibold text-gray-900">Sarah Lee</div>
                        <div class="text-xs text-gray-500">Sustainability Lead, Starbucks MY</div>
                    </div>
                </div>
            </div>

            {{-- Ahmad Rizal --}}
            <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                <div class="flex items-center gap-1 mb-4">
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed mb-6">
                    "The ROI was clear within the first month. Our Mixue outlets in Georgetown saw customer return rates jump 40%. The competitor deterrent pricing is a game-changer for brand loyalty."
                </p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm">AR</div>
                    <div>
                        <div class="text-sm font-semibold text-gray-900">Ahmad Rizal</div>
                        <div class="text-xs text-gray-500">Regional Manager, Mixue Malaysia</div>
                    </div>
                </div>
            </div>

            {{-- Pn. Nurul Huda --}}
            <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                <div class="flex items-center gap-1 mb-4">
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed mb-6">
                    "As a local authority, we needed data-driven waste management. Mobius gave us real-time fill levels, route optimization, and measurable environmental impact. A model for other councils."
                </p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-sm">NH</div>
                    <div>
                        <div class="text-sm font-semibold text-gray-900">Pn. Nurul Huda</div>
                        <div class="text-xs text-gray-500">Director of Environment, MBPP</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     CONTACT / REGISTRATION CTA
     ═══════════════════════════════════════════════════════════════════ --}}
<section id="contact" class="py-24 bg-gradient-to-br from-green-50 via-emerald-50 to-white">
    <div class="max-w-7xl mx-auto px-6">
        {{-- Header --}}
        <div class="text-center max-w-2xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-semibold tracking-wide mb-4">
                GET STARTED
            </div>
            <h2 class="text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight">
                Ready to close the loop?
            </h2>
            <p class="mt-4 text-gray-600 text-lg">
                Join the growing network of Malaysian brands making recycling smarter.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 max-w-5xl mx-auto">
            {{-- Left: benefits --}}
            <div class="flex flex-col justify-center">
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Fast Onboarding</h3>
                            <p class="text-sm text-gray-600 mt-1">Go live in under 48 hours. We handle installation, configuration, and staff training at no extra cost.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">No Lock-in</h3>
                            <p class="text-sm text-gray-600 mt-1">Month-to-month contracts with no hidden fees. Scale up or down as your needs change.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Dedicated Support</h3>
                            <p class="text-sm text-gray-600 mt-1">Local Malaysian support team available via WhatsApp, email, and phone. Average response time under 2 hours.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: registration form --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Register your interest</h3>

                @if(session('success'))
                    <div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('registration.brand.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                        <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}" required
                               class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="e.g. Mixue Malaysia Sdn Bhd">
                        @error('company_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1">Contact Name</label>
                        <input type="text" name="contact_name" id="contact_name" value="{{ old('contact_name') }}" required
                               class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Your full name">
                        @error('contact_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email') }}" required
                               class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="you@company.com">
                        @error('contact_email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="tel" name="contact_phone" id="contact_phone" value="{{ old('contact_phone') }}"
                               class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="+60 12-345 6789">
                        @error('contact_phone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" id="type" required
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Select type...</option>
                            <option value="beverage_chain" {{ old('type') === 'beverage_chain' ? 'selected' : '' }}>Beverage Chain / Franchise</option>
                            <option value="independent_cafe" {{ old('type') === 'independent_cafe' ? 'selected' : '' }}>Independent Cafe</option>
                            <option value="food_court" {{ old('type') === 'food_court' ? 'selected' : '' }}>Food Court / Mall</option>
                            <option value="university" {{ old('type') === 'university' ? 'selected' : '' }}>University / Campus</option>
                            <option value="government" {{ old('type') === 'government' ? 'selected' : '' }}>Government / Municipal</option>
                            <option value="other" {{ old('type') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Tell us about your needs</label>
                        <textarea name="description" id="description" rows="3"
                                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 resize-none"
                                  placeholder="Number of outlets, locations, timeline...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="w-full px-6 py-3 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition-colors shadow-sm">
                        Submit Registration
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     FOOTER
     ═══════════════════════════════════════════════════════════════════ --}}
<footer class="bg-gray-900 text-gray-400 py-16">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
            {{-- Brand --}}
            <div class="md:col-span-2">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-white">Mobius</span>
                </div>
                <p class="text-sm leading-relaxed max-w-sm">
                    AI-powered smart recycling bins for beverage brands. Turning every cup disposal into a loyalty moment, one scan at a time.
                </p>
            </div>

            {{-- Quick Links --}}
            <div>
                <h4 class="text-sm font-semibold text-white uppercase tracking-wide mb-4">Quick Links</h4>
                <ul class="space-y-2">
                    <li><a href="#how-it-works" class="text-sm hover:text-white transition-colors">How It Works</a></li>
                    <li><a href="#features" class="text-sm hover:text-white transition-colors">Features</a></li>
                    <li><a href="#pricing" class="text-sm hover:text-white transition-colors">Pricing</a></li>
                    <li><a href="{{ route('registration.brand.create') }}" class="text-sm hover:text-white transition-colors">Register</a></li>
                    <li><a href="{{ route('login') }}" class="text-sm hover:text-white transition-colors">Sign In</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h4 class="text-sm font-semibold text-white uppercase tracking-wide mb-4">Contact</h4>
                <ul class="space-y-2">
                    <li class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                        </svg>
                        hello@mobius.my
                    </li>
                    <li class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                        </svg>
                        +60 4-123 4567
                    </li>
                    <li class="flex items-start gap-2 text-sm">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        George Town, Penang, Malaysia
                    </li>
                </ul>
            </div>
        </div>

        {{-- Copyright --}}
        <div class="mt-12 pt-8 border-t border-gray-800 text-center text-sm">
            &copy; {{ date('Y') }} Mobius Smart Recycling. All rights reserved.
        </div>
    </div>
</footer>

</x-layouts.app>

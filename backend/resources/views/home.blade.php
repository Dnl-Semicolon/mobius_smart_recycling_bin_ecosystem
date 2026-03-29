<x-layouts.app title="Mobius — Smart Recycling Ecosystem">

{{-- ═══════════════════════════════════════════════════════════════════
     STICKY NAVIGATION
     ═══════════════════════════════════════════════════════════════════ --}}
<nav x-data="{ open: false, scrolled: false }"
     @scroll.window="scrolled = window.scrollY > 50"
     :class="scrolled ? 'bg-white/80 backdrop-blur-xl shadow-sm shadow-black/5 border-b border-gray-200/50' : 'bg-transparent'"
     class="fixed top-0 inset-x-0 z-50 transition-all duration-300">
    <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <img src="{{ asset('images/mobius-icon.png') }}" alt="Mobius" class="h-8 w-8">
            <img src="{{ asset('images/mobius-wordmark.png') }}" alt="Mobius"
                 :class="scrolled ? 'brightness-0' : 'brightness-0 invert'"
                 class="h-5 transition-all duration-300">
        </a>

        {{-- Desktop links --}}
        <div class="hidden md:flex items-center gap-8">
            <a href="#how-it-works"
               :class="scrolled ? 'text-gray-600 hover:text-gray-900' : 'text-gray-300 hover:text-white'"
               class="text-sm font-medium transition-colors">How It Works</a>
            <a href="#brands"
               :class="scrolled ? 'text-gray-600 hover:text-gray-900' : 'text-gray-300 hover:text-white'"
               class="text-sm font-medium transition-colors">For Brands</a>
            <a href="#agencies"
               :class="scrolled ? 'text-gray-600 hover:text-gray-900' : 'text-gray-300 hover:text-white'"
               class="text-sm font-medium transition-colors">For Collectors</a>
            <a href="{{ route('subscribe.plans') }}"
               :class="scrolled ? 'text-gray-600 hover:text-gray-900' : 'text-gray-300 hover:text-white'"
               class="text-sm font-medium transition-colors">Pricing</a>
        </div>

        {{-- Desktop CTAs --}}
        <div class="hidden md:flex items-center gap-3">
            @guest
                <a href="{{ route('login') }}"
                   :class="scrolled ? 'text-gray-700 hover:text-gray-900' : 'text-gray-300 hover:text-white'"
                   class="text-sm font-medium transition-colors">Sign In</a>
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-500 transition-colors shadow-lg shadow-emerald-900/20">
                    Get Started
                </a>
            @else
                <a href="{{ url('/') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-500 transition-colors shadow-lg shadow-emerald-900/20">
                    Dashboard
                </a>
            @endguest
        </div>

        {{-- Mobile hamburger --}}
        <button @click="open = !open"
                :class="scrolled ? 'text-gray-700' : 'text-white'"
                class="md:hidden p-2 -mr-2 transition-colors" aria-label="Toggle menu">
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
         class="md:hidden bg-white/95 backdrop-blur-xl border-b border-gray-200/50 shadow-lg">
        <div class="px-6 py-4 space-y-1">
            <a href="#how-it-works" @click="open = false" class="block py-2.5 text-sm font-medium text-gray-700 hover:text-emerald-600">How It Works</a>
            <a href="#brands" @click="open = false" class="block py-2.5 text-sm font-medium text-gray-700 hover:text-emerald-600">For Brands</a>
            <a href="#agencies" @click="open = false" class="block py-2.5 text-sm font-medium text-gray-700 hover:text-emerald-600">For Collectors</a>
            <a href="{{ route('subscribe.plans') }}" class="block py-2.5 text-sm font-medium text-gray-700 hover:text-emerald-600">Pricing</a>
            <div class="pt-3 border-t border-gray-100 flex flex-col gap-2">
                @guest
                    <a href="{{ route('login') }}" class="block py-2.5 text-sm font-medium text-gray-700">Sign In</a>
                    <a href="{{ route('register') }}" class="block py-2.5 px-4 text-center rounded-full bg-emerald-600 text-white text-sm font-semibold">Get Started</a>
                @else
                    <a href="{{ url('/') }}" class="block py-2.5 px-4 text-center rounded-full bg-emerald-600 text-white text-sm font-semibold">Dashboard</a>
                @endguest
            </div>
        </div>
    </div>
</nav>

{{-- ═══════════════════════════════════════════════════════════════════
     HERO SECTION
     ═══════════════════════════════════════════════════════════════════ --}}
<section class="relative min-h-screen flex items-center justify-center bg-gray-950 overflow-hidden">
    {{-- Gradient orbs --}}
    <div class="hero-orb w-[500px] h-[500px] bg-emerald-500/20 -top-48 -right-24" style="animation-delay: 0s;"></div>
    <div class="hero-orb w-[400px] h-[400px] bg-teal-400/15 bottom-0 -left-32" style="animation-delay: -7s;"></div>
    <div class="hero-orb w-[300px] h-[300px] bg-emerald-600/10 top-1/3 left-1/2" style="animation-delay: -14s;"></div>

    {{-- Grid pattern overlay --}}
    <div class="absolute inset-0 opacity-[0.03]"
         style="background-image: linear-gradient(rgba(255,255,255,.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.1) 1px, transparent 1px); background-size: 64px 64px;"></div>

    <div class="relative z-10 max-w-4xl mx-auto px-6 text-center pt-20">
        {{-- Eyebrow badge --}}
        <div class="hero-animate inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 mb-8">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="text-emerald-400 text-xs font-semibold tracking-wide uppercase">AI-Powered Recycling</span>
        </div>

        {{-- Headline --}}
        <h1 class="hero-animate hero-animate-delay-1 text-5xl sm:text-6xl md:text-7xl font-extrabold text-white tracking-tight leading-[1.08]">
            Recycling that<br>
            <span class="bg-gradient-to-r from-emerald-400 via-teal-400 to-emerald-300 bg-clip-text text-transparent">rewards everyone</span>
        </h1>

        {{-- Subtitle --}}
        <p class="hero-animate hero-animate-delay-2 mt-6 text-lg md:text-xl text-gray-400 max-w-2xl mx-auto leading-relaxed">
            Smart bins that see what you throw away, reward you with points, and give brands
            real-time recycling analytics. Powered by computer vision and route optimization.
        </p>

        {{-- CTAs --}}
        <div class="hero-animate hero-animate-delay-3 mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('register') }}"
               class="group w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-full bg-emerald-600 text-white font-semibold hover:bg-emerald-500 transition-all shadow-xl shadow-emerald-900/30 hover:shadow-emerald-900/40 hover:-translate-y-0.5">
                Create Free Account
                <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
            <a href="#brands"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-full border border-gray-700 text-gray-300 font-semibold hover:border-gray-500 hover:text-white transition-all">
                Partner With Us
            </a>
        </div>

        {{-- Social proof --}}
        <div class="hero-animate hero-animate-delay-4 mt-16 flex items-center justify-center gap-6 text-gray-500 text-sm">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                <span>AI-Powered Detection</span>
            </div>
            <span class="text-gray-700">|</span>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                <span>Built in Malaysia</span>
            </div>
        </div>
    </div>

    {{-- Bottom gradient fade --}}
    <div class="absolute bottom-0 inset-x-0 h-32 bg-gradient-to-t from-white to-transparent"></div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     LIVE IMPACT STATS
     ═══════════════════════════════════════════════════════════════════ --}}
<section class="relative bg-white py-20 -mt-1"
         x-data="{
            stats: { total_detections: 0, pickups_completed: 0, active_bins: 0, outlets_served: 0 },
            targets: null,
            counted: false,
            formatNumber(n) {
                return n.toLocaleString();
            },
            animateCount(key) {
                const target = this.targets[key];
                if (!target) return;
                const duration = 2000;
                const start = performance.now();
                const step = (now) => {
                    const elapsed = now - start;
                    const progress = Math.min(elapsed / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    this.stats[key] = Math.round(target * eased);
                    if (progress < 1) requestAnimationFrame(step);
                };
                requestAnimationFrame(step);
            },
            startCounting() {
                if (this.counted || !this.targets) return;
                this.counted = true;
                ['total_detections', 'pickups_completed', 'active_bins', 'outlets_served']
                    .forEach(key => this.animateCount(key));
            }
         }"
         x-init="
            fetch('/api/v1/public/stats')
                .then(r => r.json())
                .then(json => { targets = json.data; })
                .catch(() => { targets = { total_detections: 0, pickups_completed: 0, active_bins: 0, outlets_served: 0 }; });

            const obs = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) { startCounting(); obs.disconnect(); }
            }, { threshold: 0.3 });
            obs.observe($el);
         ">
    <div class="max-w-5xl mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 md:gap-12">
            {{-- Items sorted --}}
            <div class="text-center reveal">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                </div>
                <p class="text-3xl md:text-4xl font-bold text-gray-900 tabular-nums" x-text="formatNumber(stats.total_detections)">0</p>
                <p class="mt-1 text-sm text-gray-500 font-medium">Items Sorted</p>
            </div>

            {{-- Pickups completed --}}
            <div class="text-center reveal reveal-delay-1">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                </div>
                <p class="text-3xl md:text-4xl font-bold text-gray-900 tabular-nums" x-text="formatNumber(stats.pickups_completed)">0</p>
                <p class="mt-1 text-sm text-gray-500 font-medium">Pickups Completed</p>
            </div>

            {{-- Active bins --}}
            <div class="text-center reveal reveal-delay-2">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.302m5.304 0a3.75 3.75 0 010 5.302m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.808-3.808-9.98 0-13.788m13.788 0c3.808 3.808 3.808 9.98 0 13.788M12 12h.008v.008H12V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                </div>
                <p class="text-3xl md:text-4xl font-bold text-gray-900 tabular-nums" x-text="formatNumber(stats.active_bins)">0</p>
                <p class="mt-1 text-sm text-gray-500 font-medium">Active Bins</p>
            </div>

            {{-- Outlets served --}}
            <div class="text-center reveal reveal-delay-3">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.15c0 .415.336.75.75.75z"/></svg>
                </div>
                <p class="text-3xl md:text-4xl font-bold text-gray-900 tabular-nums" x-text="formatNumber(stats.outlets_served)">0</p>
                <p class="mt-1 text-sm text-gray-500 font-medium">Outlets Served</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     HOW IT WORKS
     ═══════════════════════════════════════════════════════════════════ --}}
<section id="how-it-works" class="py-24 bg-gray-50">
    <div class="max-w-5xl mx-auto px-6">
        <div class="text-center mb-16 reveal">
            <p class="text-sm font-semibold text-emerald-600 tracking-wide uppercase mb-3">How It Works</p>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 tracking-tight">Three steps. Zero effort.</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-8 md:gap-12">
            {{-- Step 1 --}}
            <div class="relative text-center reveal reveal-delay-1">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-600 text-white text-2xl font-bold mb-6 shadow-lg shadow-emerald-600/20">1</div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Drop</h3>
                <p class="text-gray-600 leading-relaxed">Toss your cup into any Mobius bin. The built-in camera activates the moment something enters.</p>
                {{-- Connector line (hidden on mobile) --}}
                <div class="hidden md:block absolute top-8 left-[calc(50%+40px)] w-[calc(100%-80px)] border-t-2 border-dashed border-gray-300"></div>
            </div>

            {{-- Step 2 --}}
            <div class="relative text-center reveal reveal-delay-2">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-600 text-white text-2xl font-bold mb-6 shadow-lg shadow-emerald-600/20">2</div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Detect</h3>
                <p class="text-gray-600 leading-relaxed">Our YOLOv11 model identifies the waste type and recognises the cup brand in under a second.</p>
                <div class="hidden md:block absolute top-8 left-[calc(50%+40px)] w-[calc(100%-80px)] border-t-2 border-dashed border-gray-300"></div>
            </div>

            {{-- Step 3 --}}
            <div class="text-center reveal reveal-delay-3">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-600 text-white text-2xl font-bold mb-6 shadow-lg shadow-emerald-600/20">3</div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Reward</h3>
                <p class="text-gray-600 leading-relaxed">Points land in your account instantly. Recycle at a brand's bin with their cup for a loyalty bonus.</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     FOR CUSTOMERS
     ═══════════════════════════════════════════════════════════════════ --}}
<section id="customers" class="py-24 bg-white">
    <div class="max-w-5xl mx-auto px-6">
        <div class="grid md:grid-cols-2 items-center gap-12 md:gap-16">
            {{-- Text --}}
            <div class="reveal">
                <p class="text-sm font-semibold text-emerald-600 tracking-wide uppercase mb-3">For Recyclers</p>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 tracking-tight mb-4">Earn rewards every time you recycle</h2>
                <p class="text-gray-600 leading-relaxed mb-8">
                    Every item you sort earns points. Build daily streaks for multipliers,
                    and redeem your balance for real vouchers from partner brands.
                </p>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-700">Earn 2 to 15 points per item based on waste type</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-700">Build recycling streaks for milestone bonuses</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-700">Redeem points for real vouchers from partner brands</span>
                    </li>
                </ul>
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-emerald-600 text-white font-semibold hover:bg-emerald-500 transition-colors shadow-lg shadow-emerald-900/20">
                    Create Free Account
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>

            {{-- Visual card --}}
            <div class="reveal reveal-delay-2">
                <div class="bg-gray-50 rounded-3xl p-8 border border-gray-200/60">
                    <div class="space-y-4">
                        {{-- Points breakdown --}}
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Points per item</p>
                            <div class="space-y-2.5">
                                @foreach([['Paper Cup', 15, 'bg-emerald-500'], ['Plastic Cup', 12, 'bg-teal-500'], ['Liquid Waste', 8, 'bg-blue-500'], ['Lid', 5, 'bg-amber-500'], ['Straw', 3, 'bg-orange-400'], ['Napkin', 2, 'bg-gray-400']] as [$label, $points, $color])
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-2 h-2 rounded-full {{ $color }}"></span>
                                            <span class="text-sm text-gray-700">{{ $label }}</span>
                                        </div>
                                        <span class="text-sm font-bold text-gray-900">{{ $points }} pts</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        {{-- Streak indicator --}}
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Daily Streak</p>
                                    <p class="text-2xl font-bold text-gray-900 mt-1">7 days</p>
                                </div>
                                <div class="flex gap-1">
                                    @for($i = 0; $i < 7; $i++)
                                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     FOR BRANDS
     ═══════════════════════════════════════════════════════════════════ --}}
<section id="brands" class="py-24 bg-gray-950">
    <div class="max-w-5xl mx-auto px-6">
        <div class="grid md:grid-cols-2 items-center gap-12 md:gap-16">
            {{-- Visual card (left on desktop) --}}
            <div class="order-2 md:order-1 reveal">
                <div class="bg-gray-900 rounded-3xl p-8 border border-gray-800">
                    <div class="space-y-4">
                        {{-- Brand loyalty card --}}
                        <div class="bg-gray-800/50 rounded-2xl p-5 border border-gray-700/50">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Cup Brand Loyalty</p>
                            <div class="space-y-3">
                                <div>
                                    <div class="flex items-center justify-between text-sm mb-1.5">
                                        <span class="text-emerald-400 font-medium">Brand Match</span>
                                        <span class="text-gray-400">64%</span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-gray-700">
                                        <div class="h-full rounded-full bg-emerald-500" style="width: 64%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-sm mb-1.5">
                                        <span class="text-amber-400 font-medium">Unidentified</span>
                                        <span class="text-gray-400">24%</span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-gray-700">
                                        <div class="h-full rounded-full bg-amber-500" style="width: 24%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-sm mb-1.5">
                                        <span class="text-red-400 font-medium">Competitor</span>
                                        <span class="text-gray-400">12%</span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-gray-700">
                                        <div class="h-full rounded-full bg-red-500" style="width: 12%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Multiplier card --}}
                        <div class="bg-gray-800/50 rounded-2xl p-5 border border-gray-700/50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Brand Multiplier</p>
                                    <p class="text-2xl font-bold text-white mt-1">1.5x</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Deterrent</p>
                                    <p class="text-2xl font-bold text-red-400 mt-1">0.3x</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-3">Customers earn more at your bins. Competitor cups earn less.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Text (right on desktop) --}}
            <div class="order-1 md:order-2 reveal reveal-delay-1">
                <p class="text-sm font-semibold text-emerald-400 tracking-wide uppercase mb-3">For Brands</p>
                <h2 class="text-3xl md:text-4xl font-bold text-white tracking-tight mb-4">Turn recycling into brand loyalty</h2>
                <p class="text-gray-400 leading-relaxed mb-8">
                    Place branded Mobius bins at your outlets. Our AI detects your cups automatically
                    and rewards your customers with bonus points — while penalising competitor cups.
                </p>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-400 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-300">AI detects your brand on cups automatically</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-400 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-300">Reward customers who recycle at your stores</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-400 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-300">Real-time analytics on brand match vs. competitor</span>
                    </li>
                </ul>
                <a href="{{ route('registration.brand.create') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-emerald-600 text-white font-semibold hover:bg-emerald-500 transition-colors shadow-lg shadow-emerald-900/30">
                    Register Your Brand
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     FOR COLLECTORS
     ═══════════════════════════════════════════════════════════════════ --}}
<section id="agencies" class="py-24 bg-white">
    <div class="max-w-5xl mx-auto px-6">
        <div class="grid md:grid-cols-2 items-center gap-12 md:gap-16">
            {{-- Text --}}
            <div class="reveal">
                <p class="text-sm font-semibold text-emerald-600 tracking-wide uppercase mb-3">For Collection Agencies</p>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 tracking-tight mb-4">Optimized routes, efficient collections</h2>
                <p class="text-gray-600 leading-relaxed mb-8">
                    No more guessing which bins need pickup. Our system monitors fill levels in real time,
                    groups nearby bins into efficient routes, and dispatches your collectors automatically.
                </p>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-700">AI-optimized multi-stop collection routes via VROOM</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-700">Real-time bin fill monitoring with auto-dispatch at 80%</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-700">Zone-based management with GPS-verified pickups</span>
                    </li>
                </ul>
                <a href="{{ route('registration.agency.create') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-emerald-600 text-white font-semibold hover:bg-emerald-500 transition-colors shadow-lg shadow-emerald-900/20">
                    Register Your Agency
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>

            {{-- Visual card --}}
            <div class="reveal reveal-delay-2">
                <div class="bg-gray-50 rounded-3xl p-8 border border-gray-200/60">
                    <div class="space-y-4">
                        {{-- Route preview --}}
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Active Route</p>
                            <div class="space-y-3">
                                @foreach([['Georgetown Zone A', '3 bins', 'bg-emerald-500', '2.4 km'], ['Bayan Lepas', '5 bins', 'bg-blue-500', '8.1 km'], ['Jelutong', '2 bins', 'bg-amber-500', '4.7 km']] as [$zone, $bins, $color, $dist])
                                    <div class="flex items-center gap-3">
                                        <span class="w-3 h-3 rounded-full {{ $color }} shrink-0"></span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900">{{ $zone }}</p>
                                            <p class="text-xs text-gray-500">{{ $bins }}</p>
                                        </div>
                                        <span class="text-xs text-gray-400 font-medium">{{ $dist }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        {{-- Stats row --}}
                        <div class="grid grid-cols-3 gap-3">
                            @foreach([['Stops', '10'], ['Distance', '15.2 km'], ['ETA', '47 min']] as [$label, $value])
                                <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100 text-center">
                                    <p class="text-lg font-bold text-gray-900">{{ $value }}</p>
                                    <p class="text-xs text-gray-500">{{ $label }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     TECHNOLOGY
     ═══════════════════════════════════════════════════════════════════ --}}
<section class="py-24 bg-gray-50">
    <div class="max-w-5xl mx-auto px-6">
        <div class="text-center mb-16 reveal">
            <p class="text-sm font-semibold text-emerald-600 tracking-wide uppercase mb-3">The Stack</p>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 tracking-tight">Built on serious technology</h2>
            <p class="mt-4 text-gray-600 max-w-2xl mx-auto">Not a toy demo. Real AI models, real routing engines, real hardware.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            {{-- Computer Vision --}}
            <div class="reveal reveal-delay-1 bg-white rounded-2xl p-7 border border-gray-200/60 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Computer Vision</h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-4">YOLOv11 trained on custom waste dataset. Detects waste type and cup brand logo in real time on Raspberry Pi hardware.</p>
                <div class="flex flex-wrap gap-2">
                    <span class="px-2.5 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-600">YOLOv11</span>
                    <span class="px-2.5 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-600">OpenAI Vision</span>
                    <span class="px-2.5 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-600">Roboflow</span>
                </div>
            </div>

            {{-- Route Optimization --}}
            <div class="reveal reveal-delay-2 bg-white rounded-2xl p-7 border border-gray-200/60 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Route Optimization</h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-4">OSRM for distance matrices on Malaysian roads. VROOM solves the Vehicle Routing Problem for multi-stop collection runs.</p>
                <div class="flex flex-wrap gap-2">
                    <span class="px-2.5 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-600">OSRM</span>
                    <span class="px-2.5 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-600">VROOM</span>
                    <span class="px-2.5 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-600">OpenStreetMap</span>
                </div>
            </div>

            {{-- IoT / Hardware --}}
            <div class="reveal reveal-delay-3 bg-white rounded-2xl p-7 border border-gray-200/60 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">IoT Hardware</h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-4">Raspberry Pi powered bins with camera, fill-level sensors, and offline-first architecture. Buffers data locally when offline.</p>
                <div class="flex flex-wrap gap-2">
                    <span class="px-2.5 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-600">Raspberry Pi</span>
                    <span class="px-2.5 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-600">FastAPI</span>
                    <span class="px-2.5 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-600">SQLite</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     PRICING CTA
     ═══════════════════════════════════════════════════════════════════ --}}
<section id="pricing" class="py-24 bg-gray-950 relative overflow-hidden">
    {{-- Subtle gradient orb --}}
    <div class="hero-orb w-[600px] h-[600px] bg-emerald-600/10 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2" style="animation-delay: -5s;"></div>

    <div class="relative z-10 max-w-3xl mx-auto px-6 text-center reveal">
        <p class="text-sm font-semibold text-emerald-400 tracking-wide uppercase mb-3">Pricing</p>
        <h2 class="text-3xl md:text-4xl font-bold text-white tracking-tight mb-4">Simple, transparent pricing</h2>
        <p class="text-gray-400 text-lg leading-relaxed mb-10">
            Personal accounts are free forever. Brand and agency subscriptions start at RM49/month
            with analytics, priority support, and custom multipliers.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('subscribe.plans') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-full bg-emerald-600 text-white font-semibold hover:bg-emerald-500 transition-all shadow-xl shadow-emerald-900/30">
                View Plans & Pricing
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
            <a href="{{ route('register') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-full border border-gray-700 text-gray-300 font-semibold hover:border-gray-500 hover:text-white transition-all">
                Get Started Free
            </a>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════
     FOOTER
     ═══════════════════════════════════════════════════════════════════ --}}
<footer class="bg-gray-950 border-t border-gray-800/50 pt-16 pb-8">
    <div class="max-w-5xl mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-10 mb-12">
            {{-- Brand column --}}
            <div class="col-span-2 md:col-span-1">
                <div class="flex items-center gap-2.5 mb-4">
                    <img src="{{ asset('images/mobius-icon.png') }}" alt="Mobius" class="h-8 w-8">
                    <img src="{{ asset('images/mobius-wordmark.png') }}" alt="Mobius" class="h-5 brightness-0 invert">
                </div>
                <p class="text-sm text-gray-500 leading-relaxed">
                    AI-powered smart recycling bins. Built in Penang, Malaysia.
                </p>
            </div>

            {{-- Platform links --}}
            <div>
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Platform</h4>
                <ul class="space-y-2.5">
                    <li><a href="#how-it-works" class="text-sm text-gray-500 hover:text-white transition-colors">How It Works</a></li>
                    <li><a href="#customers" class="text-sm text-gray-500 hover:text-white transition-colors">For Recyclers</a></li>
                    <li><a href="#brands" class="text-sm text-gray-500 hover:text-white transition-colors">For Brands</a></li>
                    <li><a href="#agencies" class="text-sm text-gray-500 hover:text-white transition-colors">For Collectors</a></li>
                </ul>
            </div>

            {{-- Company links --}}
            <div>
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Get Started</h4>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('register') }}" class="text-sm text-gray-500 hover:text-white transition-colors">Create Account</a></li>
                    <li><a href="{{ route('registration.brand.create') }}" class="text-sm text-gray-500 hover:text-white transition-colors">Register Brand</a></li>
                    <li><a href="{{ route('registration.agency.create') }}" class="text-sm text-gray-500 hover:text-white transition-colors">Register Agency</a></li>
                    <li><a href="{{ route('subscribe.plans') }}" class="text-sm text-gray-500 hover:text-white transition-colors">Pricing</a></li>
                </ul>
            </div>

            {{-- Account links --}}
            <div>
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Account</h4>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-white transition-colors">Sign In</a></li>
                    @auth
                        <li><a href="{{ url('/') }}" class="text-sm text-gray-500 hover:text-white transition-colors">Dashboard</a></li>
                        <li><a href="{{ route('subscribe.plans') }}" class="text-sm text-gray-500 hover:text-white transition-colors">Manage Subscription</a></li>
                    @endauth
                </ul>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="border-t border-gray-800/50 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-gray-600">&copy; {{ date('Y') }} Mobius Vision Sdn Bhd. All rights reserved.</p>
            <div class="flex items-center gap-2 text-xs text-gray-600">
                <img src="{{ asset('images/flag-malaysia.svg') }}" alt="Malaysia" class="w-4 h-3">
                <span>Made in Malaysia</span>
            </div>
        </div>
    </div>
</footer>

{{-- ═══════════════════════════════════════════════════════════════════
     SCROLL REVEAL OBSERVER
     ═══════════════════════════════════════════════════════════════════ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Scroll reveal
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal').forEach(function (el) {
        observer.observe(el);
    });
});
</script>

{{-- Override body bg for this page --}}
<style>
    body { background-color: #030712 !important; }
</style>

</x-layouts.app>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Mobius Store' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-inter min-h-screen bg-gray-50">
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

    @php
        $authUser = auth()->user();
        $currentOutlet = $authUser->outlets->first();
        $currentBrand = $currentOutlet?->brand;
        $brandColor = $currentBrand?->primary_color ?? '#10b981';
    @endphp

    {{-- Store owner sidebar --}}
    <div
        id="sidebar-backdrop"
        class="fixed inset-0 z-20 bg-black/20 opacity-0 pointer-events-none transition-opacity duration-200"
        data-sidebar-backdrop
    ></div>

    <aside
        id="store-sidebar"
        class="fixed top-2 bottom-2 left-2 z-30 flex w-80 flex-col bg-white border border-gray-200 rounded-xl shadow-lg shadow-black/8 -translate-x-[calc(100%+3rem)] transition-transform duration-200 ease-out"
        data-sidebar
    >
        <div class="flex items-center justify-between h-14 px-4 border-b border-gray-200/60 rounded-t-xl">
            <a href="{{ route('store.dashboard') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/mobius-icon.png') }}" alt="Mobius" class="w-8 h-8 object-contain">
                <span class="font-semibold text-sm text-gray-900">Store Portal</span>
            </a>
            <button
                type="button"
                class="p-1.5 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors duration-100 cursor-pointer"
                data-sidebar-close
            >
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>

        {{-- Brand card --}}
        @if ($currentBrand)
            @php $isHQUser = $authUser->id === $currentBrand->user_id; @endphp
            <div class="mx-3 mt-3 p-3 rounded-lg border border-gray-200/80 bg-gray-50/60">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background-color: {{ $brandColor }}20;">
                        <x-heroicon-s-building-storefront class="w-4 h-4" style="color: {{ $brandColor }};" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-gray-900 truncate">{{ $currentBrand->name }}</p>
                        @if ($isHQUser)
                            <p class="text-[10px] text-gray-500 truncate">{{ $currentBrand->outlets()->count() }} {{ Str::plural('outlet', $currentBrand->outlets()->count()) }}</p>
                        @else
                            <p class="text-[10px] text-gray-500 truncate">{{ $currentOutlet?->name }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
            @php
                $navItems = [
                    ['route' => 'store.dashboard', 'label' => 'Dashboard', 'icon' => 'heroicon-o-home', 'exact' => true],
                    ['route' => 'store.rewards.index', 'label' => 'Rewards', 'icon' => 'heroicon-o-gift'],
                ];
                if (($isHQUser ?? false) && Route::has('store.staff.index')) {
                    $navItems[] = ['route' => 'store.staff.index', 'label' => 'Staff', 'icon' => 'heroicon-o-user-group'];
                }
                $navItems[] = ['route' => 'store.analytics', 'label' => 'Analytics', 'icon' => 'heroicon-o-chart-bar'];
                $navItems[] = ['route' => 'store.profile.edit', 'label' => 'Settings', 'icon' => 'heroicon-o-cog-6-tooth'];
            @endphp

            @foreach ($navItems as $item)
                @php
                    $isActive = isset($item['exact']) && $item['exact']
                        ? request()->routeIs($item['route'])
                        : request()->routeIs($item['route'] . '*') || request()->routeIs($item['route']);
                @endphp
                <a
                    href="{{ route($item['route']) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-100 {{ $isActive ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                >
                    <x-dynamic-component :component="$item['icon']" class="w-5 h-5 shrink-0" />
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- Pin sidebar toggle --}}
        <div class="hidden lg:block px-3 pb-3 border-t border-gray-200/60">
            <button
                type="button"
                data-sidebar-pin
                class="flex items-center gap-2.5 w-full px-3 py-2 mt-1 rounded-md text-xs text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 16 16" fill="none">
                    <rect x="1.5" y="2" width="13" height="12" rx="2" stroke="currentColor" stroke-width="1.25"/>
                    <line x1="6" y1="2" x2="6" y2="14" stroke="currentColor" stroke-width="1.25"/>
                </svg>
                <span data-pin-label>Pin sidebar</span>
            </button>
        </div>
    </aside>

    {{-- Main content --}}
    <main class="min-h-screen transition-[margin-left] duration-200 ease-out" data-main-content>
        <header class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-gray-200/60">
            <div class="flex items-center h-14 px-4 lg:px-6 gap-3">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <button type="button" class="topbar-icon-btn shrink-0" data-sidebar-open data-sidebar-toggle title="Open navigation">
                        <x-heroicon-o-bars-3 class="w-5 h-5" />
                    </button>
                    <a href="{{ route('store.dashboard') }}" class="shrink-0 flex items-center" title="Dashboard">
                        <img src="{{ asset('images/mobius-icon.png') }}" alt="Mobius" class="w-8 h-8 object-contain">
                    </a>
                    @if (isset($back))
                        <div class="shrink-0">{{ $back }}</div>
                    @endif
                    <h1 class="text-sm font-medium text-gray-900 truncate">{{ $header ?? '' }}</h1>
                </div>
                <div class="flex items-center gap-4 shrink-0">
                    @if (isset($actions))
                        <div class="flex items-center gap-2">{{ $actions }}</div>
                    @endif

                    {{-- Avatar menu --}}
                    <div class="relative" data-topbar-dropdown>
                        <button type="button" data-topbar-trigger class="flex items-center justify-center h-10 rounded-full focus:outline-none cursor-pointer" title="User menu">
                            <span class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200/60 hover:ring-emerald-300 transition-all duration-150">
                                {{ strtoupper(substr($authUser->name, 0, 1)) }}{{ strtoupper(substr(str_contains($authUser->name, ' ') ? explode(' ', $authUser->name)[1] : '', 0, 1)) }}
                            </span>
                        </button>
                        <div
                            data-topbar-panel
                            class="absolute right-0 z-50 mt-2 w-64 rounded-lg border border-gray-300 bg-white py-2 shadow-lg shadow-black/12 opacity-0 pointer-events-none translate-y-[-4px] transition-all duration-150"
                        >
                            <div class="flex items-center gap-3 px-4 py-1.5">
                                <span class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center text-sm font-semibold text-emerald-700 shrink-0">
                                    {{ strtoupper(substr($authUser->name, 0, 1)) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $authUser->name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $authUser->email }}</p>
                                </div>
                            </div>
                            <div class="my-2 border-t border-gray-200"></div>
                            <a href="{{ route('store.profile.edit') }}" class="topbar-dropdown-item">
                                <x-heroicon-o-user class="w-4 h-4 text-gray-500" />
                                Profile
                            </a>
                            <div class="my-2 border-t border-gray-200"></div>
                            <form action="{{ route('logout') }}" method="POST" class="px-2">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 px-2.5 py-[7px] text-sm text-left rounded-md text-red-600 hover:bg-red-50 cursor-pointer transition-colors duration-75">
                                    <x-heroicon-o-arrow-right-start-on-rectangle class="w-4 h-4" />
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="p-4 lg:p-6">
            {{ $slot }}
        </div>
    </main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var sidebar = document.querySelector('[data-sidebar]');
    var backdrop = document.querySelector('[data-sidebar-backdrop]');
    var openBtns = document.querySelectorAll('[data-sidebar-open]');
    var closeBtns = document.querySelectorAll('[data-sidebar-close]');
    var pinBtn = document.querySelector('[data-sidebar-pin]');
    var pinLabel = document.querySelector('[data-pin-label]');
    var sidebarOpen = false;
    var isPinned = localStorage.getItem('store-sidebar-pinned') === '1';
    var HIDDEN = '-translate-x-[calc(100%+3rem)]';
    var VISIBLE = 'translate-x-0';

    function openSidebar() {
        if (sidebarOpen) return;
        sidebarOpen = true;
        sidebar.classList.remove(HIDDEN);
        sidebar.classList.add(VISIBLE);
        backdrop.classList.remove('opacity-0', 'pointer-events-none');
        backdrop.classList.add('opacity-100', 'pointer-events-auto');
    }

    function closeSidebar() {
        sidebarOpen = false;
        sidebar.classList.remove(VISIBLE);
        sidebar.classList.add(HIDDEN);
        backdrop.classList.remove('opacity-100', 'pointer-events-auto');
        backdrop.classList.add('opacity-0', 'pointer-events-none');
    }

    function updatePinUI() {
        if (isPinned) {
            document.body.classList.add('sidebar-pinned');
            if (pinLabel) pinLabel.textContent = 'Unpin sidebar';
            if (pinBtn) { pinBtn.title = 'Unpin sidebar'; pinBtn.classList.add('text-emerald-600'); pinBtn.classList.remove('text-gray-400'); }
        } else {
            document.body.classList.remove('sidebar-pinned');
            if (pinLabel) pinLabel.textContent = 'Pin sidebar';
            if (pinBtn) { pinBtn.title = 'Pin sidebar'; pinBtn.classList.remove('text-emerald-600'); pinBtn.classList.add('text-gray-400'); }
        }
    }

    if (isPinned) {
        updatePinUI();
        if (window.innerWidth >= 1024) {
            sidebar.style.transitionDuration = '0s';
            sidebar.classList.remove(HIDDEN);
            sidebar.classList.add(VISIBLE);
            sidebarOpen = true;
            requestAnimationFrame(function () { requestAnimationFrame(function () { sidebar.style.transitionDuration = ''; }); });
        }
    }

    if (pinBtn) {
        pinBtn.addEventListener('click', function (e) {
            e.preventDefault();
            isPinned = !isPinned;
            localStorage.setItem('store-sidebar-pinned', isPinned ? '1' : '0');
            updatePinUI();
            if (isPinned) { backdrop.classList.remove('opacity-100', 'pointer-events-auto'); backdrop.classList.add('opacity-0', 'pointer-events-none'); }
            else { closeSidebar(); }
        });
    }

    openBtns.forEach(function (btn) { btn.addEventListener('click', function (e) { e.preventDefault(); openSidebar(); }); });
    closeBtns.forEach(function (btn) { btn.addEventListener('click', function (e) { e.preventDefault(); closeSidebar(); }); });
    backdrop.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && sidebarOpen && !(isPinned && window.innerWidth >= 1024)) { closeSidebar(); } });

    // Topbar dropdowns
    document.querySelectorAll('[data-topbar-dropdown]').forEach(function (container) {
        var trigger = container.querySelector('[data-topbar-trigger]');
        var panel = container.querySelector('[data-topbar-panel]');
        var isOpen = false;
        function open() { if (isOpen) return; isOpen = true; panel.style.opacity = '1'; panel.style.pointerEvents = 'auto'; panel.style.transform = 'translateY(0)'; }
        function close() { if (!isOpen) return; isOpen = false; panel.style.opacity = '0'; panel.style.pointerEvents = 'none'; panel.style.transform = 'translateY(-4px)'; }
        trigger.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); isOpen ? close() : open(); });
        document.addEventListener('click', function (e) { if (isOpen && !container.contains(e.target)) { close(); } });
        container.addEventListener('keydown', function (e) { if (e.key === 'Escape' && isOpen) { e.preventDefault(); close(); trigger.focus(); } });
    });
});
</script>
</body>
</html>

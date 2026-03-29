<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Mobius Admin' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
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

    {{-- Sidebar (overlay) --}}
    <x-admin.sidebar />

    {{-- Main content (full width, no margin shift) --}}
    <main class="min-h-screen transition-[margin-left] duration-200 ease-out" data-main-content>
        {{-- Top header bar --}}
        <header class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-gray-200/60">
            <div class="flex items-center h-14 px-4 lg:px-6 gap-3">
                {{-- Left side: sidebar toggle + logo + back + title --}}
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    {{-- Sidebar toggle (hamburger) --}}
                    <button
                        type="button"
                        class="topbar-icon-btn shrink-0"
                        data-sidebar-open
                        data-sidebar-toggle
                        data-testid="sidebar-toggle"
                        title="Open navigation"
                    >
                        <x-heroicon-o-bars-3 class="w-5 h-5" />
                    </button>

                    {{-- Logo (always visible, like GitHub) --}}
                    <a href="{{ route('admin.dashboard') }}" class="shrink-0 flex items-center" title="Dashboard">
                        <img src="{{ asset('images/mobius-icon.png') }}" alt="Mobius" class="w-8 h-8 object-contain">
                    </a>

                    {{-- Back button (optional slot) --}}
                    @if (isset($back))
                        <div class="shrink-0">
                            {{ $back }}
                        </div>
                    @endif

                    {{-- Page title --}}
                    <h1 class="text-sm font-medium text-gray-900 truncate">
                        {{ $header ?? '' }}
                    </h1>
                </div>

                {{-- Right side: page actions + create + notifications + avatar --}}
                @php $authUser = auth()->user(); @endphp
                <div class="flex items-center gap-4 shrink-0">
                    {{-- Page-specific actions --}}
                    @if (isset($actions))
                        <div class="flex items-center gap-2">
                            {{ $actions }}
                        </div>
                    @endif

                    {{-- Quick-create dropdown --}}
                    <div class="relative" data-topbar-dropdown>
                        <button
                            type="button"
                            class="topbar-icon-btn topbar-icon-btn--wide"
                            data-topbar-trigger
                            data-testid="quick-create-trigger"
                            title="Create new..."
                        >
                            <x-heroicon-o-plus class="w-[18px] h-[18px]" />
                            <svg class="w-2.5 h-2.5 opacity-60 ml-0.5" viewBox="0 0 10 6" fill="currentColor"><path d="M1 0.5L5 4.5L9 0.5H1Z"/></svg>
                        </button>
                        <div
                            data-topbar-panel
                            class="absolute right-0 z-50 mt-2 w-52 rounded-lg border border-gray-300 bg-white py-2 shadow-lg shadow-black/12 opacity-0 pointer-events-none translate-y-[-4px] transition-all duration-150"
                        >
                            <a href="{{ route('admin.outlets.create') }}" class="topbar-dropdown-item">
                                <x-heroicon-o-building-storefront class="w-4 h-4 text-gray-500" />
                                New Outlet
                            </a>
                            <a href="{{ route('admin.bins.create') }}" class="topbar-dropdown-item">
                                <x-heroicon-o-archive-box class="w-4 h-4 text-gray-500" />
                                New Bin
                            </a>
                            <a href="{{ route('admin.users.create') }}" class="topbar-dropdown-item">
                                <x-heroicon-o-user-plus class="w-4 h-4 text-gray-500" />
                                New User
                            </a>
                        </div>
                    </div>

                    {{-- Notifications --}}
                    <a href="{{ route('admin.notifications.index') }}" class="topbar-icon-btn" title="Notifications" data-testid="notifications-bell">
                        <x-heroicon-o-bell class="w-5 h-5" />
                    </a>

                    {{-- Avatar menu dropdown --}}
                    <div class="relative" data-topbar-dropdown>
                        <button type="button" data-topbar-trigger class="flex items-center justify-center h-10 rounded-full focus:outline-none cursor-pointer" title="Open user navigation menu" data-testid="avatar-menu-trigger">
                            @if ($authUser->avatar_path)
                                <img
                                    src="{{ Storage::url($authUser->avatar_path) }}"
                                    alt="{{ $authUser->name }}"
                                    class="w-9 h-9 rounded-full object-cover ring-1 ring-gray-200/80 hover:ring-gray-300 transition-all duration-150"
                                >
                            @else
                                <span class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200/60 hover:ring-emerald-300 transition-all duration-150">
                                    {{ strtoupper(substr($authUser->name, 0, 1)) }}{{ strtoupper(substr(str_contains($authUser->name, ' ') ? explode(' ', $authUser->name)[1] : '', 0, 1)) }}
                                </span>
                            @endif
                        </button>
                        <div
                            data-topbar-panel
                            class="absolute right-0 z-50 mt-2 w-64 rounded-lg border border-gray-300 bg-white py-2 shadow-lg shadow-black/12 opacity-0 pointer-events-none translate-y-[-4px] transition-all duration-150"
                        >
                            <div class="flex items-center gap-3 px-4 py-1.5">
                                @if ($authUser->avatar_path)
                                    <img src="{{ Storage::url($authUser->avatar_path) }}" alt="{{ $authUser->name }}" class="w-9 h-9 rounded-full object-cover shrink-0">
                                @else
                                    <span class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center text-sm font-semibold text-emerald-700 shrink-0">
                                        {{ strtoupper(substr($authUser->name, 0, 1)) }}{{ strtoupper(substr(str_contains($authUser->name, ' ') ? explode(' ', $authUser->name)[1] : '', 0, 1)) }}
                                    </span>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $authUser->name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $authUser->email }}</p>
                                </div>
                            </div>
                            <div class="my-2 border-t border-gray-200"></div>
                            <a href="{{ route('admin.profile.edit') }}" class="topbar-dropdown-item">
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

        {{-- Page content --}}
        <div class="p-4 lg:p-6">
            {{ $slot }}
        </div>

        {{--
            DEV QUICK-LINKS FOOTER (commented out — no longer needed)
            <footer class="mt-16 border-t border-gray-200/60 py-6 px-4 lg:px-6" data-testid="dev-quicklinks-footer">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-400">
                    <span>&copy; {{ date('Y') }} Mobius</span>
                    <span class="hidden sm:inline text-gray-300/60">&middot;</span>
                    <a href="{{ route('admin.z-acodex-temp-workbench') }}" class="hover:text-gray-600 transition-colors">API Workbench</a>
                    <a href="{{ route('dev.api-explorer') }}" class="hover:text-gray-600 transition-colors">API Explorer</a>
                </div>
            </footer>
        --}}
    </main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Sidebar ---
    var sidebar = document.querySelector('[data-sidebar]');
    var backdrop = document.querySelector('[data-sidebar-backdrop]');
    var openBtns = document.querySelectorAll('[data-sidebar-open]');
    var closeBtns = document.querySelectorAll('[data-sidebar-close]');
    var pinBtn = document.querySelector('[data-sidebar-pin]');
    var pinLabel = document.querySelector('[data-pin-label]');
    var sidebarOpen = false;
    var isPinned = localStorage.getItem('sidebar-pinned') === '1';
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
            if (pinBtn) {
                pinBtn.title = 'Unpin sidebar';
                pinBtn.classList.add('text-emerald-600');
                pinBtn.classList.remove('text-gray-400');
            }
        } else {
            document.body.classList.remove('sidebar-pinned');
            if (pinLabel) pinLabel.textContent = 'Pin sidebar';
            if (pinBtn) {
                pinBtn.title = 'Pin sidebar';
                pinBtn.classList.remove('text-emerald-600');
                pinBtn.classList.add('text-gray-400');
            }
        }
    }

    // Apply pinned state on load (desktop only)
    if (isPinned) {
        updatePinUI();
        if (window.innerWidth >= 1024) {
            sidebar.style.transitionDuration = '0s';
            sidebar.classList.remove(HIDDEN);
            sidebar.classList.add(VISIBLE);
            sidebarOpen = true;
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    sidebar.style.transitionDuration = '';
                });
            });
        }
    }

    // Pin button click
    if (pinBtn) {
        pinBtn.addEventListener('click', function (e) {
            e.preventDefault();
            isPinned = !isPinned;
            localStorage.setItem('sidebar-pinned', isPinned ? '1' : '0');
            updatePinUI();
            if (isPinned) {
                backdrop.classList.remove('opacity-100', 'pointer-events-auto');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
            } else {
                closeSidebar();
            }
        });
    }

    openBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            openSidebar();
        });
    });

    closeBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            closeSidebar();
        });
    });

    backdrop.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebarOpen && !(isPinned && window.innerWidth >= 1024)) {
            closeSidebar();
        }
    });

    // --- Topbar dropdowns ---
    document.querySelectorAll('[data-topbar-dropdown]').forEach(function (container) {
        var trigger = container.querySelector('[data-topbar-trigger]');
        var panel = container.querySelector('[data-topbar-panel]');
        var isOpen = false;

        function open() {
            if (isOpen) return;
            isOpen = true;
            panel.style.opacity = '1';
            panel.style.pointerEvents = 'auto';
            panel.style.transform = 'translateY(0)';
        }

        function close() {
            if (!isOpen) return;
            isOpen = false;
            panel.style.opacity = '0';
            panel.style.pointerEvents = 'none';
            panel.style.transform = 'translateY(-4px)';
        }

        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            isOpen ? close() : open();
        });

        document.addEventListener('click', function (e) {
            if (isOpen && !container.contains(e.target)) {
                close();
            }
        });

        container.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen) {
                e.preventDefault();
                close();
                trigger.focus();
            }
        });
    });
});
</script>
</body>
</html>

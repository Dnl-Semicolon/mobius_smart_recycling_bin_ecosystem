<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Mobius Admin' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,500,600,700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/htmx.org@2.0.4"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="font-nunito min-h-screen bg-gray-50"
    hx-boost="true"
    x-data="{
        sidebarOpen: $persist(true).as('admin-sidebar-open'),
        mobileMenuOpen: false,
        currentPath: window.location.pathname
    }"
    @htmx:pushed-into-history.window="currentPath = window.location.pathname"
>
    {{-- Mobile overlay --}}
    <div
        x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-20 bg-black/20 lg:hidden"
        @click="mobileMenuOpen = false"
        data-testid="mobile-overlay"
        x-cloak
    ></div>

    {{-- Sidebar --}}
    <x-admin.sidebar />

    {{-- Main content --}}
    <main
        class="min-h-screen transition-all duration-200"
        :class="{ 'lg:ml-64': sidebarOpen, 'lg:ml-16': !sidebarOpen }"
    >
        {{-- Top header bar --}}
        <header class="sticky top-0 z-10 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between h-14 px-4 lg:px-6">
                {{-- Mobile menu button --}}
                <button
                    @click="mobileMenuOpen = true"
                    class="p-2 -ml-2 rounded-lg text-gray-500 hover:bg-gray-100 lg:hidden"
                    data-testid="mobile-menu-button"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Back button (optional slot) --}}
                @if (isset($back))
                    <div class="hidden lg:block">
                        {{ $back }}
                    </div>
                @endif

                {{-- Page title --}}
                <h1 class="text-lg font-semibold text-gray-900 flex-1 lg:flex-none @if(!isset($back)) lg:ml-0 @endif">
                    {{ $header ?? '' }}
                </h1>

                {{-- Actions --}}
                <div class="flex items-center gap-2">
                    {{ $actions ?? '' }}
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <div class="p-4 lg:p-6">
            {{ $slot }}
        </div>
    </main>
</body>
</html>

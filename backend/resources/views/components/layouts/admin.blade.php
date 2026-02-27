<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Mobius Admin' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/htmx.org@2.0.4"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="font-inter min-h-screen bg-gray-50"
    x-data="{
        sidebarOpen: $persist(true).as('admin-sidebar-open'),
        mobileMenuOpen: false,
        currentPath: window.location.pathname
    }"
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
        <header class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-gray-200/60">
            <div class="flex items-center justify-between h-14 px-4 lg:px-6">
                {{-- Mobile menu button --}}
                <button
                    @click="mobileMenuOpen = true"
                    class="p-2 -ml-2 rounded-lg text-gray-500 hover:bg-gray-100 lg:hidden"
                    data-testid="mobile-menu-button"
                >
                    <x-heroicon-o-bars-3 class="w-6 h-6" />
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

        {{--
            DEV QUICK-LINKS FOOTER
            =====================
            A subtle footer at the bottom of every admin page for the developer
            to quickly access API visualisation / playground Blade pages during
            Flutter mobile app integration work.

            AI AGENTS: When you build a temporary API visualisation or playground
            Blade web app (e.g. a page that renders API responses, tests endpoints,
            or previews data for the Flutter mobile app), add a link here so Daniel
            can easily navigate to it. Use the same <a> format as the examples in
            the link list below. Keep links short — just a label and a route/URL.
        --}}
        <footer class="mt-16 border-t border-gray-200/60 py-6 px-4 lg:px-6" data-testid="dev-quicklinks-footer">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-400">
                <span>&copy; {{ date('Y') }} Mobius</span>
                <span class="hidden sm:inline text-gray-300/60">&middot;</span>
                {{--
                    ADD API VISUALISATION LINKS BELOW
                    =================================
                    When an AI agent creates a Blade page for API visualisation,
                    testing, or playground use, it should add a link here.

                    Format:
                    <a href="{{ route('route.name') }}" class="hover:text-gray-600 transition-colors">Label</a>
                    <a href="/some/path" class="hover:text-gray-600 transition-colors">Label</a>

                    Example:
                    <a href="/dev/api-viewer" class="hover:text-gray-600 transition-colors">API Viewer</a>
                    <a href="{{ route('dev.detection-test') }}" class="hover:text-gray-600 transition-colors">Detection Tester</a>
                --}}
                <a href="{{ route('admin.z-acodex-temp-workbench') }}" class="hover:text-gray-600 transition-colors">API Workbench</a>
                <a href="{{ route('dev.api-explorer') }}" class="hover:text-gray-600 transition-colors">API Explorer</a>
            </div>
        </footer>
    </main>
</body>
</html>

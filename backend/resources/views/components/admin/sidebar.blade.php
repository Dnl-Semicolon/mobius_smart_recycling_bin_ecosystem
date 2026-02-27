<aside
    class="fixed inset-y-0 left-0 z-30 flex flex-col bg-white border-r border-gray-200/60 transition-all duration-200 -translate-x-full lg:translate-x-0"
    :class="{
        'w-64': sidebarOpen && !mobileMenuOpen,
        'w-16': !sidebarOpen && !mobileMenuOpen,
        'w-64 translate-x-0': mobileMenuOpen
    }"
>
    {{-- Logo/Brand --}}
    <div class="flex items-center justify-between h-14 px-4 border-b border-gray-200/60">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2" x-show="sidebarOpen">
            <span class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center">
                <x-heroicon-s-arrow-path class="w-5 h-5 text-white" />
            </span>
            <span class="font-bold text-gray-900 tracking-tight">Mobius</span>
        </a>
        <a href="{{ route('admin.dashboard') }}" x-show="!sidebarOpen && !mobileMenuOpen" x-cloak>
            <span class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center mx-auto">
                <x-heroicon-s-arrow-path class="w-5 h-5 text-white" />
            </span>
        </a>
        <button
            @click="sidebarOpen = !sidebarOpen"
            class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 hidden lg:block"
            data-testid="sidebar-toggle"
        >
            <x-heroicon-o-chevron-double-left class="w-4 h-4 transition-transform duration-200" x-bind:class="{ 'rotate-180': !sidebarOpen }" />
        </button>
        {{-- Mobile close button --}}
        <button
            @click="mobileMenuOpen = false"
            class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 lg:hidden"
        >
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
        <x-admin.sidebar-item route="admin.dashboard" label="Dashboard" :exact="true">
            <x-slot:icon><x-heroicon-o-home class="w-5 h-5" /></x-slot:icon>
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.outlets.index" label="Outlets">
            <x-slot:icon><x-heroicon-o-building-storefront class="w-5 h-5" /></x-slot:icon>
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.bins.index" label="Bins">
            <x-slot:icon><x-heroicon-o-archive-box class="w-5 h-5" /></x-slot:icon>
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.detection-events.index" label="Detections">
            <x-slot:icon><x-heroicon-o-eye class="w-5 h-5" /></x-slot:icon>
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.pickup-requests.index" label="Pickups">
            <x-slot:icon><x-heroicon-o-truck class="w-5 h-5" /></x-slot:icon>
            @if (($pendingPickupCount = \App\Models\PickupRequest::pending()->count()) > 0)
                <x-slot:badge>{{ $pendingPickupCount }}</x-slot:badge>
            @endif
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.users.index" label="Users">
            <x-slot:icon><x-heroicon-o-users class="w-5 h-5" /></x-slot:icon>
        </x-admin.sidebar-item>
    </nav>

    {{-- Footer --}}
    <div class="p-3 border-t border-gray-200/60">
        {{-- User info + logout --}}
        <div class="flex items-center gap-3 px-3 py-2" x-show="sidebarOpen">
            <a href="{{ route('admin.profile.edit') }}" class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center shrink-0 hover:bg-emerald-200 transition-colors">
                <x-heroicon-s-user class="w-4 h-4 text-emerald-700" />
            </a>
            <a href="{{ route('admin.profile.edit') }}" class="flex-1 min-w-0 hover:opacity-75 transition-opacity">
                <p class="text-sm font-medium text-gray-700 truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ auth()->user()->role->value }}</p>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 transition-colors" title="Sign out">
                    <x-heroicon-o-arrow-right-start-on-rectangle class="w-4 h-4" />
                </button>
            </form>
        </div>
        {{-- Collapsed: just the logout icon --}}
        <div x-show="!sidebarOpen && !mobileMenuOpen" x-cloak class="flex justify-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="p-2 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 transition-colors" title="Sign out">
                    <x-heroicon-o-arrow-right-start-on-rectangle class="w-5 h-5" />
                </button>
            </form>
        </div>
    </div>
</aside>

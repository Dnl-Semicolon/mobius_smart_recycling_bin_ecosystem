<aside
    hx-preserve="true"
    id="admin-sidebar"
    class="fixed inset-y-0 left-0 z-30 flex flex-col bg-white border-r border-gray-200 transition-all duration-200 -translate-x-full lg:translate-x-0"
    :class="{
        'w-64': sidebarOpen && !mobileMenuOpen,
        'w-16': !sidebarOpen && !mobileMenuOpen,
        'w-64 translate-x-0': mobileMenuOpen
    }"
>
    {{-- Logo/Brand --}}
    <div class="flex items-center justify-between h-14 px-4 border-b border-gray-200">
        <a href="{{ route('admin.dashboard') }}" class="font-bold text-gray-900" x-show="sidebarOpen">
            Mobius
        </a>
        <button
            @click="sidebarOpen = !sidebarOpen"
            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 hidden lg:block"
            data-testid="sidebar-toggle"
        >
            <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="{ 'rotate-180': !sidebarOpen }">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
        </button>
        {{-- Mobile close button --}}
        <button
            @click="mobileMenuOpen = false"
            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 lg:hidden"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
        <x-admin.sidebar-item route="admin.dashboard" label="Dashboard" :exact="true" />
        <x-admin.sidebar-item route="admin.outlets.index" label="Outlets" />
        <x-admin.sidebar-item route="admin.bins.index" label="Bins" />
        <x-admin.sidebar-item route="admin.detection-events.index" label="Detection Events" />
    </nav>
</aside>

{{-- Backdrop overlay --}}
<div
    id="sidebar-backdrop"
    class="fixed inset-0 z-20 bg-black/20 opacity-0 pointer-events-none transition-opacity duration-200"
    data-sidebar-backdrop
></div>

{{-- Sidebar panel --}}
<aside
    id="admin-sidebar"
    class="fixed top-2 bottom-2 left-2 z-30 flex w-80 flex-col bg-white border border-gray-200 rounded-xl shadow-lg shadow-black/8 -translate-x-[calc(100%+3rem)] transition-transform duration-200 ease-out"
    data-sidebar
>
    {{-- Header: Logo + close button --}}
    <div class="flex items-center justify-between h-14 px-4 border-b border-gray-200/60 rounded-t-xl">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
            <img src="{{ asset('images/mobius-icon.png') }}" alt="Mobius" class="w-8 h-8 object-contain">
            <img src="{{ asset('images/mobius-wordmark.png') }}" alt="Mobius" class="h-5 object-contain">
        </a>
        <button
            type="button"
            class="p-1.5 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors duration-100 cursor-pointer"
            data-sidebar-close
            title="Close sidebar"
        >
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
        <x-admin.sidebar-item route="admin.dashboard" label="Dashboard" :exact="true">
            <x-slot:icon><x-heroicon-o-home class="w-5 h-5" /></x-slot:icon>
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.organizations.index" label="Organizations">
            <x-slot:icon><x-heroicon-o-building-office-2 class="w-5 h-5" /></x-slot:icon>
            @if (($pendingRegCount = \App\Models\RegistrationRequest::where('status', 'pending')->count()) > 0)
                <x-slot:badge>{{ $pendingRegCount }}</x-slot:badge>
            @endif
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.outlets.index" label="Outlets">
            <x-slot:icon><x-heroicon-o-building-storefront class="w-5 h-5" /></x-slot:icon>
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.bins.index" label="Bins">
            <x-slot:icon><x-heroicon-o-archive-box class="w-5 h-5" /></x-slot:icon>
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.bin-sessions.index" label="Bin Sessions">
            <x-slot:icon><x-heroicon-o-clock class="w-5 h-5" /></x-slot:icon>
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.detection-events.index" label="Detections">
            <x-slot:icon><x-heroicon-o-eye class="w-5 h-5" /></x-slot:icon>
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.pickup-requests.index" label="Pickups">
            <x-slot:icon><x-heroicon-o-truck class="w-5 h-5" /></x-slot:icon>
            @if (($pendingPickupCount = \App\Models\PickupRequest::where('status', 'pending')->count()) > 0)
                <x-slot:badge>{{ $pendingPickupCount }}</x-slot:badge>
            @endif
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.collection-routes.index" label="Collection Routes">
            <x-slot:icon><x-heroicon-o-map class="w-5 h-5" /></x-slot:icon>
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.users.index" label="Users">
            <x-slot:icon><x-heroicon-o-users class="w-5 h-5" /></x-slot:icon>
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.invitations.index" label="Invitations">
            <x-slot:icon><x-heroicon-o-envelope class="w-5 h-5" /></x-slot:icon>
            @if (($pendingInvCount = \App\Models\Invitation::where('status', 'pending')->count()) > 0)
                <x-slot:badge>{{ $pendingInvCount }}</x-slot:badge>
            @endif
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.brands.index" label="Brands">
            <x-slot:icon><x-heroicon-o-building-storefront class="w-5 h-5" /></x-slot:icon>
        </x-admin.sidebar-item>
        {{-- Brand Directory and Reports removed — not in new schema --}}
        <x-admin.sidebar-item route="admin.applications.brands.index" label="Applications">
            <x-slot:icon><x-heroicon-o-clipboard-document-check class="w-5 h-5" /></x-slot:icon>
            @php
                $pendingAppCount = 0;
                if (Schema::hasTable('brand_applications')) {
                    $pendingAppCount += \App\Models\BrandApplication::where('status', 'pending')->count();
                }
                if (Schema::hasTable('collector_agencies')) {
                    $pendingAppCount += \App\Models\CollectorAgency::where('status', 'pending')->count();
                }
            @endphp
            @if ($pendingAppCount > 0)
                <x-slot:badge>{{ $pendingAppCount }}</x-slot:badge>
            @endif
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.subscriptions.index" label="Subscriptions">
            <x-slot:icon><x-heroicon-o-credit-card class="w-5 h-5" /></x-slot:icon>
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.payments.index" label="Payments">
            <x-slot:icon><x-heroicon-o-banknotes class="w-5 h-5" /></x-slot:icon>
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.vouchers.index" label="Vouchers">
            <x-slot:icon><x-heroicon-o-gift class="w-5 h-5" /></x-slot:icon>
        </x-admin.sidebar-item>
        <x-admin.sidebar-item route="admin.plans.index" label="Plans">
            <x-slot:icon><x-heroicon-o-rectangle-group class="w-5 h-5" /></x-slot:icon>
        </x-admin.sidebar-item>
    </nav>

    {{-- Pin sidebar toggle (desktop only) --}}
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

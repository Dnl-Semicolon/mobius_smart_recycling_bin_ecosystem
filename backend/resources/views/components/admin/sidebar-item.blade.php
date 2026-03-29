@props(['route', 'label', 'icon' => null, 'badge' => null, 'exact' => false])

@php
    $isActive = $exact ? request()->routeIs($route) : request()->routeIs($route . '*');
@endphp

<a
    href="{{ route($route) }}"
    class="sidebar-nav-item {{ $isActive ? 'sidebar-nav-item--active' : '' }}"
>
    @if ($icon)
        <span class="shrink-0 w-5 h-5 text-gray-600">
            {{ $icon }}
        </span>
    @endif
    <span class="flex-1">{{ $label }}</span>
    @isset($badge)
        <span class="text-xs font-medium bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full">{{ $badge }}</span>
    @endisset
</a>

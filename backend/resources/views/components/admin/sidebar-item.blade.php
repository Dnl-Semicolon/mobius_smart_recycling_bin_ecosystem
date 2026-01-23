@props(['route', 'label', 'match' => null])

@php
    $isActive = request()->routeIs($match ?? $route);
@endphp

<a
    href="{{ route($route) }}"
    @class([
        'flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors',
        'bg-gray-100 text-gray-900 font-medium' => $isActive,
        'text-gray-600 hover:bg-gray-50 hover:text-gray-900' => !$isActive,
    ])
>
    <span x-show="sidebarOpen">{{ $label }}</span>
    <span x-show="!sidebarOpen" class="w-full text-center" x-cloak>{{ Str::substr($label, 0, 1) }}</span>
</a>

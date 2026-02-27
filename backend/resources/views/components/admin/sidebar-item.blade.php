@props(['route', 'label', 'icon' => null, 'badge' => null, 'exact' => false])

@php
    $href = route($route);
    $path = parse_url($href, PHP_URL_PATH);
@endphp

<a
    href="{{ $href }}"
    x-data="{ get isActive() { return {{ $exact ? 'true' : 'false' }} ? currentPath === '{{ $path }}' : currentPath.startsWith('{{ $path }}') } }"
    :class="isActive ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors"
>
    @if ($icon)
        <span class="shrink-0 w-5 h-5" :class="isActive ? 'text-emerald-600' : 'text-gray-400'">
            {{ $icon }}
        </span>
    @endif
    <span x-show="sidebarOpen" class="flex-1">{{ $label }}</span>
    @isset($badge)
        <span x-show="sidebarOpen" class="text-xs font-medium bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full">{{ $badge }}</span>
    @endisset
    @if (!$icon)
        <span x-show="!sidebarOpen" class="w-full text-center" x-cloak>{{ Str::substr($label, 0, 1) }}</span>
    @endif
</a>

@props(['route', 'label', 'exact' => false])

@php
    $href = route($route);
    $path = parse_url($href, PHP_URL_PATH);
@endphp

<a
    href="{{ $href }}"
    x-data="{ get isActive() { return {{ $exact ? 'true' : 'false' }} ? currentPath === '{{ $path }}' : currentPath.startsWith('{{ $path }}') } }"
    :class="isActive ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
    class="flex items-center px-3 py-2.5 rounded-lg text-sm transition-colors"
>
    <span x-show="sidebarOpen">{{ $label }}</span>
    <span x-show="!sidebarOpen" class="w-full text-center" x-cloak>{{ Str::substr($label, 0, 1) }}</span>
</a>

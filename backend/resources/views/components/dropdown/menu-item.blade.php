@props([
    'href' => null,
    'icon' => null,
    'formAction' => null,
    'method' => 'POST',
    'danger' => false,
])

@php
    $textColor = $danger ? 'text-red-600' : 'text-gray-800';
    $hoverBg = $danger ? 'hover:bg-red-50' : 'hover:bg-gray-100';
    $classes = "flex items-center gap-3 mx-2 px-2.5 py-[7px] text-sm text-left rounded-md {$textColor} {$hoverBg} focus:bg-gray-100 focus:outline-none transition-colors duration-75 cursor-pointer";
@endphp

@if ($formAction)
    <form action="{{ $formAction }}" method="POST">
        @csrf
        @if (strtoupper($method) !== 'POST')
            @method($method)
        @endif
        <button type="submit" role="menuitem" tabindex="-1" class="{{ $classes }}">
            @if ($icon)
                <x-dynamic-component :component="$icon" class="h-4 w-4 shrink-0 text-gray-500" />
            @endif
            <span class="truncate">{{ $slot }}</span>
        </button>
    </form>
@elseif ($href)
    <a href="{{ $href }}" role="menuitem" tabindex="-1" class="{{ $classes }}">
        @if ($icon)
            <x-dynamic-component :component="$icon" class="h-4 w-4 shrink-0 text-gray-500" />
        @endif
        <span class="truncate">{{ $slot }}</span>
    </a>
@else
    <button type="button" role="menuitem" tabindex="-1" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-dynamic-component :component="$icon" class="h-4 w-4 shrink-0 text-gray-500" />
        @endif
        <span class="truncate">{{ $slot }}</span>
    </button>
@endif

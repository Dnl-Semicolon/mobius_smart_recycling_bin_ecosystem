@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
])

@php
    $baseClasses = 'pill inline-flex items-center justify-center gap-2 text-sm font-medium focus-visible:scale-[1.02] focus-visible:ring-2 focus-visible:ring-offset-2';

    $variants = [
        'primary' => 'bg-black text-white hover:bg-gray-800 focus-visible:bg-gray-800 focus-visible:ring-black/30 focus-visible:ring-offset-white',
        'secondary' => 'bg-gray-100 text-gray-700 hover:bg-gray-200 focus-visible:bg-gray-200 focus-visible:ring-gray-400/30 focus-visible:ring-offset-white',
        'ghost' => 'bg-transparent text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus-visible:bg-gray-100 focus-visible:text-gray-900 focus-visible:ring-gray-400/30 focus-visible:ring-offset-transparent',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus-visible:bg-red-700 focus-visible:ring-red-500/30 focus-visible:ring-offset-white',
    ];

    $variantClasses = $variants[$variant] ?? $variants['primary'];
    $classes = $baseClasses . ' ' . $variantClasses;
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif

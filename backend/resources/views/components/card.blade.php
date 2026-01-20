@props([
    'variant' => 'glass',
    'interactive' => false,
    'href' => null,
])

@php
    $variants = [
        'glass' => 'glass',
        'subtle' => 'glass-subtle',
        'plain' => 'bg-white border border-gray-200 shadow-sm',
    ];

    $variantClasses = $variants[$variant] ?? $variants['glass'];
    $interactiveClasses = $interactive ? 'card-interactive' : '';
    $classes = $variantClasses . ' rounded-2xl p-6 ' . $interactiveClasses;
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes . ' block']) }}>
        {{ $slot }}
    </a>
@else
    <div {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </div>
@endif

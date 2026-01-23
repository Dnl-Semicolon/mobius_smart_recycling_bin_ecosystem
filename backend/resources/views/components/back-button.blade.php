@props([
    'href' => null,
])

@php
    $destination = $href ?? url()->previous();
@endphp

<a
    href="{{ $destination }}"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 focus-visible:bg-gray-100 focus-visible:text-gray-900 focus-visible:ring-2 focus-visible:ring-black/10 focus-visible:ring-offset-2']) }}
    aria-label="Go back"
>
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
    </svg>
</a>

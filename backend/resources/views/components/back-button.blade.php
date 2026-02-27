@props([
    'href' => null,
])

@php
    $destination = $href ?? url()->previous();
@endphp

<a
    href="{{ $destination }}"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center p-2 rounded-lg text-gray-400 hover:text-gray-900 hover:bg-gray-100 focus-visible:bg-gray-100 focus-visible:text-gray-900 focus-visible:ring-2 focus-visible:ring-emerald-500/10 focus-visible:ring-offset-2']) }}
    aria-label="Go back"
>
    <x-heroicon-o-arrow-left class="w-5 h-5" />
</a>

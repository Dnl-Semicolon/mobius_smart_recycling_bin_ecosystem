@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'hint' => null,
])

@php
    $inputId = $attributes->get('id', $name);
    $hasError = $errors->has($name);

    $baseClasses = 'w-full rounded-xl border bg-white/60 px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-gray-300 focus:bg-white focus:ring-2 focus:ring-black/5';

    $stateClasses = $hasError
        ? 'border-red-300 bg-red-50/50 text-red-900 placeholder:text-red-300 focus:border-red-400 focus:ring-red-500/10'
        : 'border-gray-200/80';

    $classes = $baseClasses . ' ' . $stateClasses;
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-600">
            {{ $label }}
        </label>
    @endif

    <input
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        aria-invalid="{{ $hasError ? 'true' : 'false' }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >

    @if ($hasError)
        <p class="text-xs text-red-600 font-medium">{{ $errors->first($name) }}</p>
    @elseif ($hint)
        <p class="text-xs text-gray-400">{{ $hint }}</p>
    @endif
</div>

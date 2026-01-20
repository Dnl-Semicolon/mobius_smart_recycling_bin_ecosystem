@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => 'Select an option',
    'hint' => null,
])

@php
    $selectId = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $selectedValue = old($name, $value);

    $baseClasses = 'w-full rounded-xl border bg-white/60 px-4 py-2.5 text-sm text-gray-900 focus:border-gray-300 focus:bg-white focus:ring-2 focus:ring-black/5 appearance-none';

    $stateClasses = $hasError
        ? 'border-red-300 bg-red-50/50 text-red-900 focus:border-red-400 focus:ring-red-500/10'
        : 'border-gray-200/80';

    $classes = $baseClasses . ' ' . $stateClasses;
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $selectId }}" class="block text-sm font-medium text-gray-600">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        <select
            id="{{ $selectId }}"
            name="{{ $name }}"
            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
            {{ $attributes->merge(['class' => $classes]) }}
        >
            @if ($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach ($options as $optionValue => $optionLabel)
                @php
                    $actualValue = is_numeric($optionValue) ? $optionLabel : $optionValue;
                    $displayLabel = $optionLabel;
                @endphp
                <option value="{{ $actualValue }}" @selected($selectedValue === $actualValue)>
                    {{ $displayLabel }}
                </option>
            @endforeach
        </select>

        {{-- Dropdown arrow --}}
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>

    @if ($hasError)
        <p class="text-xs text-red-600 font-medium">{{ $errors->first($name) }}</p>
    @elseif ($hint)
        <p class="text-xs text-gray-400">{{ $hint }}</p>
    @endif
</div>

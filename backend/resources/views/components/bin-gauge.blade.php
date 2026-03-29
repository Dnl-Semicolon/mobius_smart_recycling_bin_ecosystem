@props([
    'value' => 0,
    'size' => 160,
])

@php
    // Scale everything relative to size — designed at 160px baseline
    $scale = $size / 160;
    $strokeWidth = max(4, round(12 * $scale));
    $radius = ($size / 2) - $strokeWidth - 2;
    $circumference = 2 * M_PI * $radius;
    $arc = $circumference * 0.75; // 270 degrees
    $offset = $arc - ($arc * $value / 100);
    $color = match(true) {
        $value >= 80 => '#ef4444',
        $value >= 50 => '#eab308',
        default => '#10b981',
    };
    $isCompact = $size <= 64;
@endphp

<div {{ $attributes->merge(['class' => 'relative inline-flex items-center justify-center']) }} style="width: {{ $size }}px; height: {{ $size }}px;">
    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}" class="transform rotate-[135deg]">
        {{-- Background track --}}
        <circle
            cx="{{ $size / 2 }}"
            cy="{{ $size / 2 }}"
            r="{{ $radius }}"
            fill="none"
            stroke="#e5e7eb"
            stroke-width="{{ $strokeWidth }}"
            stroke-dasharray="{{ $arc }} {{ $circumference }}"
            stroke-linecap="round"
        />
        {{-- Value arc --}}
        <circle
            cx="{{ $size / 2 }}"
            cy="{{ $size / 2 }}"
            r="{{ $radius }}"
            fill="none"
            stroke="{{ $color }}"
            stroke-width="{{ $strokeWidth }}"
            stroke-dasharray="{{ $arc }} {{ $circumference }}"
            stroke-dashoffset="{{ $offset }}"
            stroke-linecap="round"
            class="transition-all duration-700 ease-out"
        />
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center">
        @if ($isCompact)
            <span class="text-xs font-bold" style="color: {{ $color }}">{{ $value }}%</span>
        @else
            <span class="text-3xl font-bold" style="color: {{ $color }}">{{ $value }}%</span>
            <span class="text-xs text-gray-400 mt-0.5">Fill Level</span>
        @endif
    </div>
</div>

@props([
    'bin',
    'active' => 'overview',
])

@php
    $tabs = [
        'overview' => ['label' => 'Overview', 'route' => route('admin.bins.show', $bin), 'icon' => 'eye'],
        'detections' => ['label' => 'Detections', 'route' => route('admin.bins.detections', $bin), 'icon' => 'camera'],
        'pickups' => ['label' => 'Pickups', 'route' => route('admin.bins.pickups', $bin), 'icon' => 'truck'],
        'analytics' => ['label' => 'Analytics', 'route' => route('admin.bins.analytics', $bin), 'icon' => 'chart-bar'],
    ];
@endphp

<nav class="flex gap-1 bg-gray-100/80 rounded-xl p-1">
    @foreach ($tabs as $key => $tab)
        <a
            href="{{ $tab['route'] }}"
            class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-all {{ $active === $key ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-white/50' }}"
        >
            @if ($tab['icon'] === 'eye')
                <x-heroicon-o-eye class="w-4 h-4" />
            @elseif ($tab['icon'] === 'camera')
                <x-heroicon-o-camera class="w-4 h-4" />
            @elseif ($tab['icon'] === 'truck')
                <x-heroicon-o-truck class="w-4 h-4" />
            @elseif ($tab['icon'] === 'chart-bar')
                <x-heroicon-o-chart-bar class="w-4 h-4" />
            @endif
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>

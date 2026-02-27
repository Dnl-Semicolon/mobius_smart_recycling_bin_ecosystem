<x-layouts.admin title="Detection Events">
    <x-slot:header>
        Detections
    </x-slot:header>

    {{-- Success Flash --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-50 border border-emerald-200/60 px-4 py-3 text-sm text-emerald-700">
            <x-heroicon-s-check-circle class="w-5 h-5 shrink-0" />
            {{ session('success') }}
        </div>
    @endif

    {{-- Simulate Detection --}}
    <div x-data="{ open: false }" class="mb-6">
        <button @click="open = !open" type="button" class="flex items-center gap-2 text-sm font-medium text-emerald-600 hover:text-emerald-700 mb-2">
            <x-heroicon-o-plus-circle class="w-5 h-5" />
            Simulate Detection
            <x-heroicon-o-chevron-down class="w-3.5 h-3.5 transition-transform" x-bind:class="{ 'rotate-180': open }" />
        </button>
        <div x-show="open" x-cloak x-transition>
            <x-card class="p-4">
                <form method="POST" action="{{ route('admin.detection-events.simulate') }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Bin</label>
                            <select name="bin_id" required class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-400 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                                <option value="">Select bin...</option>
                                @foreach ($bins as $bin)
                                    <option value="{{ $bin->id }}">{{ $bin->serial_number }}</option>
                                @endforeach
                            </select>
                            @error('bin_id')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Waste Type</label>
                            <select name="waste_type" required class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-400 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                                <option value="">Select type...</option>
                                @foreach ($wasteTypes as $type)
                                    <option value="{{ $type->value }}">{{ ucwords(str_replace('_', ' ', $type->value)) }}</option>
                                @endforeach
                            </select>
                            @error('waste_type')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Confidence (leave blank for random 75-99%)</label>
                        <input type="number" name="confidence" min="0" max="100" placeholder="75-99" class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-400 focus:ring focus:ring-emerald-200 focus:ring-opacity-50" />
                    </div>
                    <x-button type="submit" class="w-full justify-center">
                        <x-heroicon-o-beaker class="w-4 h-4" />
                        Simulate
                    </x-button>
                </form>
            </x-card>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.detection-events.index') }}" class="mb-6">
        <div class="space-y-3">
            <div class="grid grid-cols-2 gap-2">
                <select
                    name="bin"
                    class="rounded-xl border-gray-200 text-sm focus:border-emerald-400 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"
                >
                    <option value="">All Bins</option>
                    @foreach ($bins as $bin)
                        <option value="{{ $bin->id }}" @selected(request('bin') == $bin->id)>
                            {{ $bin->serial_number }}
                        </option>
                    @endforeach
                </select>
                <select
                    name="waste_type"
                    class="rounded-xl border-gray-200 text-sm focus:border-emerald-400 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"
                >
                    <option value="">All Types</option>
                    @foreach ($wasteTypes as $type)
                        <option value="{{ $type->value }}" @selected(request('waste_type') === $type->value)>
                            {{ ucwords(str_replace('_', ' ', $type->value)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <x-input
                    name="from"
                    type="date"
                    :value="request('from')"
                    placeholder="From date"
                />
                <x-input
                    name="to"
                    type="date"
                    :value="request('to')"
                    placeholder="To date"
                />
            </div>
            <div class="flex gap-2">
                <div class="flex-1">
                    <label class="block text-xs text-gray-500 mb-1">Min Confidence</label>
                    <input
                        type="range"
                        name="min_confidence"
                        min="0"
                        max="100"
                        value="{{ request('min_confidence', 0) }}"
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-emerald-600"
                        oninput="document.getElementById('confidence_display').textContent = this.value + '%'"
                    >
                </div>
                <span id="confidence_display" class="text-sm text-gray-600 w-12 text-right self-end">
                    {{ request('min_confidence', 0) }}%
                </span>
            </div>
            <x-button type="submit" variant="secondary" class="w-full justify-center">
                <x-heroicon-o-funnel class="w-4 h-4" />
                Filter
            </x-button>
        </div>
    </form>

    @if ($events->isEmpty())
        {{-- Empty State --}}
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-eye class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-400">No detection events found</p>
        </x-card>
    @else
        {{-- Event List --}}
        <div class="space-y-3">
            @foreach ($events as $event)
                @php
                    $typeIcons = [
                        'paper_cup' => 'text-amber-500',
                        'plastic_cup' => 'text-blue-500',
                        'lid' => 'text-gray-500',
                        'straw' => 'text-pink-500',
                        'napkin' => 'text-orange-400',
                        'liquid_waste' => 'text-cyan-500',
                    ];
                    $iconColor = $event->waste_type ? ($typeIcons[$event->waste_type->value] ?? 'text-gray-400') : 'text-gray-400';
                @endphp
                <x-card :href="route('admin.detection-events.show', $event)" :interactive="true" class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0 mt-0.5">
                                <x-heroicon-s-beaker class="w-5 h-5 {{ $iconColor }}" />
                            </div>
                            <div class="min-w-0">
                                <h2 class="font-semibold text-gray-900">
                                    {{ $event->waste_type ? ucwords(str_replace('_', ' ', $event->waste_type->value)) : 'Pending Inference' }}
                                </h2>
                                <p class="text-sm text-gray-500 mt-0.5 flex items-center gap-1">
                                    <x-heroicon-o-archive-box class="w-3.5 h-3.5" />
                                    {{ $event->bin->serial_number }}
                                    @if ($event->bin->currentAssignment)
                                        <span class="text-gray-400">@</span>
                                        {{ $event->bin->currentAssignment->outlet->name }}
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $event->detected_at->format('d M Y, H:i') }}
                                    <span class="text-gray-300 mx-0.5">·</span>
                                    {{ $event->detected_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <div class="shrink-0 flex flex-col items-end gap-1.5">
                            @php
                                $confidenceColor = match(true) {
                                    $event->confidence >= 90 => 'bg-emerald-100 text-emerald-700',
                                    $event->confidence >= 70 => 'bg-yellow-100 text-yellow-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="text-xs font-medium rounded-full px-2.5 py-1 {{ $confidenceColor }}">
                                {{ $event->confidence }}%
                            </span>
                            @if ($event->image_path)
                                <span class="text-xs text-gray-400 flex items-center gap-1">
                                    <x-heroicon-o-camera class="w-3 h-3" />
                                    Image
                                </span>
                            @endif
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $events->links() }}
        </div>
    @endif
</x-layouts.admin>

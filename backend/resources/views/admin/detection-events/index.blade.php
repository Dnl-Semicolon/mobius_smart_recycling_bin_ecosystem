<x-layouts.app title="Detection Events">
    <x-slot:back>
        <x-back-button href="{{ route('admin.dashboard') }}" />
    </x-slot:back>

    <x-slot:header>
        Events
    </x-slot:header>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.detection-events.index') }}" class="mb-6">
        <div class="space-y-3">
            <div class="grid grid-cols-2 gap-2">
                <select
                    name="bin"
                    class="rounded-xl border-gray-200 text-sm focus:border-gray-300 focus:ring focus:ring-gray-200 focus:ring-opacity-50"
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
                    class="rounded-xl border-gray-200 text-sm focus:border-gray-300 focus:ring focus:ring-gray-200 focus:ring-opacity-50"
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
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                        oninput="document.getElementById('confidence_display').textContent = this.value + '%'"
                    >
                </div>
                <span id="confidence_display" class="text-sm text-gray-600 w-12 text-right self-end">
                    {{ request('min_confidence', 0) }}%
                </span>
            </div>
            <x-button type="submit" class="w-full justify-center">
                Filter
            </x-button>
        </div>
    </form>

    @if ($events->isEmpty())
        {{-- Empty State --}}
        <x-card variant="subtle" class="text-center py-16">
            <p class="text-gray-400">No detection events found</p>
        </x-card>
    @else
        {{-- Event List --}}
        <div class="space-y-3">
            @foreach ($events as $event)
                <x-card :href="route('admin.detection-events.show', $event)" :interactive="true" class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-gray-900">
                                {{ ucwords(str_replace('_', ' ', $event->waste_type->value)) }}
                            </h2>
                            <p class="text-sm text-gray-500 mt-0.5">
                                {{ $event->bin->serial_number }}
                                @if ($event->bin->currentAssignment)
                                    <span class="text-gray-400">@</span>
                                    {{ $event->bin->currentAssignment->outlet->name }}
                                @endif
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $event->detected_at->format('d M Y, H:i') }}
                                <span class="text-gray-300">·</span>
                                {{ $event->detected_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="shrink-0 flex flex-col items-end gap-1">
                            @php
                                $confidenceColor = match(true) {
                                    $event->confidence >= 90 => 'bg-green-100 text-green-700',
                                    $event->confidence >= 70 => 'bg-yellow-100 text-yellow-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="text-xs rounded-full px-2.5 py-1 {{ $confidenceColor }}">
                                {{ $event->confidence }}%
                            </span>
                            @if ($event->image_path)
                                <span class="text-xs text-gray-400">Has image</span>
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
</x-layouts.app>

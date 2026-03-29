<x-layouts.admin :title="$bin->serial_number . ' — Detections'">
    <x-slot:back>
        <x-back-button href="{{ route('admin.bins.show', $bin) }}" />
    </x-slot:back>

    <x-slot:header>
        {{ $bin->serial_number }}
    </x-slot:header>

    {{-- Hero Bar (compact) --}}
    <x-card class="!p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <x-bin-gauge :value="$bin->fill_level" :size="48" />
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $bin->serial_number }}</p>
                    <p class="text-xs text-gray-500">{{ $bin->currentAssignment?->outlet->name ?? 'Unassigned' }}</p>
                </div>
            </div>
            <x-bin-tab-nav :bin="$bin" active="detections" />
        </div>
    </x-card>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.bins.detections', $bin) }}" class="mb-6">
        <div class="flex flex-wrap gap-2">
            <select
                name="waste_type"
                class="rounded-xl border-gray-200 text-sm focus:border-emerald-400 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"
            >
                <option value="">All Waste Types</option>
                @foreach ($wasteTypes as $type)
                    <option value="{{ $type->value }}" @selected(request('waste_type') === $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </select>
            <x-input
                name="min_confidence"
                type="number"
                placeholder="Min confidence %"
                :value="request('min_confidence')"
                min="0"
                max="100"
                class="w-36"
            />
            <x-input
                name="date_from"
                type="date"
                :value="request('date_from')"
            />
            <x-input
                name="date_to"
                type="date"
                :value="request('date_to')"
            />
            <x-button type="submit" variant="secondary">
                <x-heroicon-o-funnel class="w-4 h-4" />
                Filter
            </x-button>
            @if (request()->hasAny(['waste_type', 'min_confidence', 'date_from', 'date_to']))
                <x-button href="{{ route('admin.bins.detections', $bin) }}" variant="ghost">
                    Clear
                </x-button>
            @endif
        </div>
    </form>

    @if ($detections->isEmpty())
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-camera class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-400 text-lg">No detections found</p>
            <p class="text-sm text-gray-400 mt-1">Detections appear here when the AI processes items in this bin.</p>
        </x-card>
    @else
        <div class="space-y-3">
            @foreach ($detections as $detection)
                <x-card
                    :href="route('admin.detection-events.show', $detection)"
                    :interactive="true"
                    class="!p-4 border-l-4 {{ $detection->waste_type ? $detection->waste_type->borderColor() : 'border-l-gray-300' }}"
                >
                    <div class="flex items-center gap-4">
                        {{-- Image Thumbnail --}}
                        @if ($detection->image_path)
                            <div class="w-14 h-14 rounded-lg overflow-hidden bg-gray-100 shrink-0">
                                <img src="{{ Storage::url($detection->image_path) }}" alt="Detection" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="w-14 h-14 rounded-lg {{ $detection->waste_type ? $detection->waste_type->iconColor() . ' ' . $detection->waste_type->bgColor() : 'text-gray-400 bg-gray-50' }} flex items-center justify-center shrink-0">
                                <x-heroicon-s-beaker class="w-6 h-6" />
                            </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900">
                                {{ $detection->waste_type?->label() ?? 'Pending Inference' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $detection->detected_at->format('d M Y, H:i') }}
                                <span class="text-gray-300 mx-1">&middot;</span>
                                {{ $detection->detected_at->diffForHumans() }}
                            </p>
                        </div>

                        <div class="shrink-0 flex flex-col items-end gap-1">
                            <div class="flex items-center gap-2">
                                @if ($detection->weight_g)
                                    <span class="text-sm font-mono text-amber-500">{{ $detection->weight_g }}g</span>
                                @endif
                                @if ($detection->confidence)
                                    <span class="text-sm font-semibold {{ $detection->confidence >= 90 ? 'text-emerald-600' : ($detection->confidence >= 70 ? 'text-yellow-600' : 'text-gray-500') }}">
                                        {{ $detection->confidence }}%
                                    </span>
                                @endif
                            </div>
                            @if ($detection->image_path)
                                <x-heroicon-o-photo class="w-4 h-4 text-gray-300" />
                            @endif
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $detections->links() }}
        </div>
    @endif
</x-layouts.admin>

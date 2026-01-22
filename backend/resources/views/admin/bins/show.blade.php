<x-layouts.app :title="$bin->serial_number">
    <x-slot:back>
        <x-back-button href="{{ route('admin.bins.index') }}" />
    </x-slot:back>

    <x-slot:header>
        {{ $bin->serial_number }}
    </x-slot:header>

    <x-slot:actions>
        <x-button href="{{ route('admin.bins.edit', $bin) }}">
            Edit
        </x-button>
    </x-slot:actions>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 p-4 rounded-xl bg-green-50 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 rounded-xl bg-red-50 text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    @php
        $isReadyForPickup = $bin->status === \App\Enums\BinStatus::Active && $bin->fill_level >= 80;
        $fillColor = match(true) {
            $bin->fill_level >= 80 => 'bg-red-500',
            $bin->fill_level >= 50 => 'bg-yellow-500',
            default => 'bg-green-500',
        };
        $statusColors = [
            'active' => 'bg-green-100 text-green-700',
            'inactive' => 'bg-gray-100 text-gray-600',
            'maintenance' => 'bg-orange-100 text-orange-700',
        ];
    @endphp

    <div class="space-y-6">
        {{-- Status & Fill Level --}}
        <x-card>
            <div class="form-section">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-sm rounded-full px-3 py-1 {{ $statusColors[$bin->status->value] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($bin->status->value) }}
                        </span>
                        @if ($isReadyForPickup)
                            <span class="text-sm bg-red-100 text-red-700 rounded-full px-3 py-1">
                                Ready for Pickup
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Large Fill Level Indicator --}}
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-500">Fill Level</span>
                        <span class="text-2xl font-bold {{ $bin->fill_level >= 80 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $bin->fill_level }}%
                        </span>
                    </div>
                    <div class="w-full h-4 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full {{ $fillColor }} rounded-full transition-all" style="width: {{ $bin->fill_level }}%"></div>
                    </div>
                </div>

                {{-- Current Assignment --}}
                <div>
                    <h3 class="text-xs text-gray-400 uppercase tracking-wide mb-2">Current Assignment</h3>
                    @if ($bin->currentAssignment)
                        <a href="{{ route('admin.outlets.show', $bin->currentAssignment->outlet) }}" class="text-blue-600 hover:underline">
                            {{ $bin->currentAssignment->outlet->name }}
                        </a>
                        <p class="text-xs text-gray-500 mt-1">
                            Since {{ $bin->currentAssignment->assigned_at->format('d M Y, H:i') }}
                        </p>
                    @else
                        <p class="text-gray-400">Not assigned to any outlet</p>
                    @endif
                </div>
            </div>
        </x-card>

        {{-- Assignment Actions --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title">Assignment</h2>
                @if ($bin->currentAssignment)
                    <form action="{{ route('admin.bins.unassign', $bin) }}" method="POST" class="mt-4">
                        @csrf
                        <x-button type="submit" variant="secondary">
                            Unassign from {{ $bin->currentAssignment->outlet->name }}
                        </x-button>
                    </form>
                @else
                    <form action="{{ route('admin.bins.assign', $bin) }}" method="POST" class="mt-4">
                        @csrf
                        <div class="flex gap-2">
                            <select
                                name="outlet_id"
                                required
                                class="flex-1 rounded-xl border-gray-200 text-sm focus:border-gray-300 focus:ring focus:ring-gray-200 focus:ring-opacity-50"
                            >
                                <option value="">Select outlet...</option>
                                @foreach ($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                @endforeach
                            </select>
                            <x-button type="submit">
                                Assign
                            </x-button>
                        </div>
                    </form>
                @endif
            </div>
        </x-card>

        {{-- Assignment History --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title">Assignment History</h2>
                @if ($bin->assignments->isEmpty())
                    <p class="text-gray-400 text-sm">No assignment history</p>
                @else
                    <div class="space-y-3 mt-4">
                        @foreach ($bin->assignments as $assignment)
                            <div class="p-3 rounded-xl bg-gray-50 {{ !$assignment->unassigned_at ? 'ring-2 ring-green-200' : '' }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <a href="{{ route('admin.outlets.show', $assignment->outlet) }}" class="font-medium text-gray-900 hover:text-blue-600">
                                            {{ $assignment->outlet->name }}
                                        </a>
                                        @if (!$assignment->unassigned_at)
                                            <span class="text-xs bg-green-100 text-green-700 rounded-full px-2 py-0.5 ml-2">Current</span>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $assignment->assigned_at->format('d M Y, H:i') }}
                                    @if ($assignment->unassigned_at)
                                        - {{ $assignment->unassigned_at->format('d M Y, H:i') }}
                                    @else
                                        - Present
                                    @endif
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-card>

        {{-- Recent Detections --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title">Recent Detections (Last 20)</h2>
                @if ($bin->detectionEvents->isEmpty())
                    <p class="text-gray-400 text-sm">No detections recorded</p>
                @else
                    <div class="space-y-2 mt-4">
                        @foreach ($bin->detectionEvents as $detection)
                            <a
                                href="{{ route('admin.detection-events.show', $detection) }}"
                                class="block p-3 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ ucwords(str_replace('_', ' ', $detection->waste_type->value)) }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $detection->detected_at->format('d M Y, H:i') }}
                                        </p>
                                    </div>
                                    <span class="text-xs bg-gray-100 text-gray-600 rounded-full px-2 py-0.5">
                                        {{ $detection->confidence }}%
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.detection-events.index', ['bin' => $bin->id]) }}" class="text-sm text-blue-600 hover:underline">
                            View all detections for this bin →
                        </a>
                    </div>
                @endif
            </div>
        </x-card>

        {{-- Delete Action --}}
        <div class="flex justify-end">
            <form
                action="{{ route('admin.bins.destroy', $bin) }}"
                method="POST"
                hx-boost="false"
                onsubmit="return confirm('Delete this bin? This action can be undone (soft delete).')"
            >
                @csrf
                @method('DELETE')

                <x-button type="submit" variant="danger">
                    Delete
                </x-button>
            </form>
        </div>
    </div>
</x-layouts.app>

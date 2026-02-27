<x-layouts.admin :title="$bin->serial_number">
    <x-slot:back>
        <x-back-button href="{{ route('admin.bins.index') }}" />
    </x-slot:back>

    <x-slot:header>
        {{ $bin->serial_number }}
    </x-slot:header>

    <x-slot:actions>
        <x-button href="{{ route('admin.bins.edit', $bin) }}">
            <x-heroicon-o-pencil-square class="w-4 h-4" />
            Edit
        </x-button>
    </x-slot:actions>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 text-emerald-700 text-sm flex items-center gap-2">
            <x-heroicon-o-check-circle class="w-5 h-5 shrink-0" />
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 rounded-xl bg-red-50 text-red-700 text-sm flex items-center gap-2">
            <x-heroicon-o-exclamation-circle class="w-5 h-5 shrink-0" />
            {{ session('error') }}
        </div>
    @endif

    @php
        $isReadyForPickup = $bin->status === \App\Enums\BinStatus::Active && $bin->fill_level >= 80;
        $fillColor = match(true) {
            $bin->fill_level >= 80 => 'bg-red-500',
            $bin->fill_level >= 50 => 'bg-yellow-500',
            default => 'bg-emerald-500',
        };
        $statusColors = [
            'active' => 'bg-emerald-100 text-emerald-700',
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
                        <span class="text-sm font-medium rounded-full px-3 py-1 {{ $statusColors[$bin->status->value] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($bin->status->value) }}
                        </span>
                        @if ($isReadyForPickup)
                            <span class="text-sm font-medium bg-red-100 text-red-700 rounded-full px-3 py-1 flex items-center gap-1">
                                <x-heroicon-s-exclamation-triangle class="w-3.5 h-3.5" />
                                Ready for Pickup
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Pickup Request Status --}}
                @if ($bin->activePickupRequest)
                    @php $pickupRequest = $bin->activePickupRequest; @endphp
                    <div class="mt-4 mb-4 p-3 rounded-xl {{ $pickupRequest->isPending() ? 'bg-amber-50 border border-amber-200' : 'bg-blue-50 border border-blue-200' }}">
                        <div class="flex items-center gap-2">
                            @if ($pickupRequest->isPending())
                                <x-heroicon-s-clock class="w-4 h-4 text-amber-600" />
                                <span class="text-sm font-medium text-amber-700">Pickup Pending</span>
                            @else
                                <x-heroicon-s-truck class="w-4 h-4 text-blue-600" />
                                <span class="text-sm font-medium text-blue-700">
                                    Claimed by {{ $pickupRequest->claimedBy->name }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1 ml-6">
                            Requested {{ $pickupRequest->created_at->diffForHumans() }}
                            @if ($pickupRequest->isClaimed())
                                — Claimed {{ $pickupRequest->claimed_at->diffForHumans() }}
                            @endif
                        </p>
                    </div>
                @endif

                {{-- Large Fill Level Indicator --}}
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-500 flex items-center gap-1.5">
                            <x-heroicon-o-beaker class="w-4 h-4" />
                            Fill Level
                        </span>
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
                    <h3 class="text-xs text-gray-400 uppercase tracking-wide mb-2 flex items-center gap-1">
                        <x-heroicon-o-map-pin class="w-3.5 h-3.5" />
                        Current Assignment
                    </h3>
                    @if ($bin->currentAssignment)
                        <a href="{{ route('admin.outlets.show', $bin->currentAssignment->outlet) }}" class="text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center gap-1.5">
                            <x-heroicon-o-building-storefront class="w-4 h-4" />
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
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-link class="w-3.5 h-3.5" />
                    Assignment
                </h2>
                @if ($bin->currentAssignment)
                    <form action="{{ route('admin.bins.unassign', $bin) }}" method="POST" class="mt-4">
                        @csrf
                        <x-button type="submit" variant="secondary">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
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
                                class="flex-1 rounded-xl border-gray-200 text-sm focus:border-emerald-400 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"
                            >
                                <option value="">Select outlet...</option>
                                @foreach ($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                @endforeach
                            </select>
                            <x-button type="submit">
                                <x-heroicon-o-link class="w-4 h-4" />
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
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-clock class="w-3.5 h-3.5" />
                    Assignment History
                </h2>
                @if ($bin->assignments->isEmpty())
                    <p class="text-gray-400 text-sm">No assignment history</p>
                @else
                    <div class="space-y-3 mt-4">
                        @foreach ($bin->assignments as $assignment)
                            <div class="p-3 rounded-xl bg-gray-50 {{ !$assignment->unassigned_at ? 'ring-2 ring-emerald-200' : '' }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.outlets.show', $assignment->outlet) }}" class="font-medium text-gray-900 hover:text-emerald-600">
                                            {{ $assignment->outlet->name }}
                                        </a>
                                        @if (!$assignment->unassigned_at)
                                            <span class="text-xs font-medium bg-emerald-100 text-emerald-700 rounded-full px-2 py-0.5">Current</span>
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

        {{-- Pickup History --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-truck class="w-3.5 h-3.5" />
                    Pickup History
                </h2>
                @if ($bin->pickupRequests->isEmpty())
                    <p class="text-gray-400 text-sm">No pickup requests yet</p>
                @else
                    <div class="space-y-4 mt-4">
                        @foreach ($bin->pickupRequests as $pickup)
                            @php
                                $statusConfig = match($pickup->status) {
                                    \App\Enums\PickupStatus::Pending => ['bg' => 'bg-amber-100 text-amber-700', 'dot' => 'bg-amber-400'],
                                    \App\Enums\PickupStatus::Claimed => ['bg' => 'bg-blue-100 text-blue-700', 'dot' => 'bg-blue-400'],
                                    \App\Enums\PickupStatus::Completed => ['bg' => 'bg-emerald-100 text-emerald-700', 'dot' => 'bg-emerald-400'],
                                };
                            @endphp
                            <a href="{{ route('admin.pickup-requests.show', $pickup) }}" class="block p-3 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold rounded-full px-2.5 py-0.5 {{ $statusConfig['bg'] }}">
                                        {{ ucfirst($pickup->status->value) }}
                                    </span>
                                    <span class="text-xs text-gray-400">#{{ $pickup->id }}</span>
                                </div>
                                <div class="flex items-center gap-4 text-xs text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        {{ $pickup->created_at->format('d M, H:i') }}
                                    </span>
                                    @if ($pickup->claimed_at)
                                        <span class="flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                            {{ $pickup->claimedBy?->name ?? 'Unknown' }}
                                        </span>
                                    @endif
                                    @if ($pickup->completed_at)
                                        <span class="flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            {{ $pickup->completed_at->format('d M, H:i') }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-card>

        {{-- Recent Detections --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-eye class="w-3.5 h-3.5" />
                    Recent Detections (Last 20)
                </h2>
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
                                            {{ $detection->waste_type ? ucwords(str_replace('_', ' ', $detection->waste_type->value)) : 'Pending Inference' }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $detection->detected_at->format('d M Y, H:i') }}
                                        </p>
                                    </div>
                                    <span class="text-xs font-medium bg-gray-100 text-gray-600 rounded-full px-2 py-0.5">
                                        {{ $detection->confidence }}%
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.detection-events.index', ['bin' => $bin->id]) }}" class="text-sm text-emerald-600 hover:text-emerald-700 inline-flex items-center gap-1">
                            View all detections
                            <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
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
                    <x-heroicon-o-trash class="w-4 h-4" />
                    Delete
                </x-button>
            </form>
        </div>
    </div>
</x-layouts.admin>

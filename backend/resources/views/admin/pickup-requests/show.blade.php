<x-layouts.admin :title="'Pickup #' . $pickupRequest->id">
    <x-slot:back>
        <x-back-button href="{{ route('admin.pickup-requests.index') }}" />
    </x-slot:back>

    <x-slot:header>
        Pickup Request #{{ $pickupRequest->id }}
    </x-slot:header>

    @php
        $bin = $pickupRequest->bin;
        $outlet = $bin->currentAssignment?->outlet;
        $statusConfig = match($pickupRequest->status) {
            \App\Enums\PickupStatus::Pending => ['bg' => 'bg-amber-100 text-amber-700', 'label' => 'Pending'],
            \App\Enums\PickupStatus::Claimed => ['bg' => 'bg-blue-100 text-blue-700', 'label' => 'Claimed'],
            \App\Enums\PickupStatus::Completed => ['bg' => 'bg-emerald-100 text-emerald-700', 'label' => 'Completed'],
            \App\Enums\PickupStatus::Cancelled => ['bg' => 'bg-red-100 text-red-700', 'label' => 'Cancelled'],
        };
    @endphp

    <div class="space-y-6">
        {{-- Status Banner --}}
        <x-card class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm font-semibold rounded-full px-4 py-1.5 {{ $statusConfig['bg'] }}">
                        {{ $statusConfig['label'] }}
                    </span>
                </div>
                <span class="text-sm text-gray-400">
                    Created {{ $pickupRequest->created_at->format('d M Y, H:i') }}
                </span>
            </div>
        </x-card>

        {{-- Timeline --}}
        <x-card class="p-6">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-6 flex items-center gap-1.5">
                <x-heroicon-o-clock class="w-4 h-4" />
                Timeline
            </h2>
            <div class="relative pl-10">
                {{-- Vertical line --}}
                <div class="absolute left-[11px] top-1 bottom-1 w-0.5 bg-gray-200"></div>

                {{-- Step 1: Requested --}}
                <div class="relative mb-8">
                    <div class="absolute -left-10 w-6 h-6 rounded-full bg-amber-100 border-2 border-amber-400 flex items-center justify-center">
                        <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Pickup Requested</p>
                        <p class="text-sm text-gray-500 mt-0.5">{{ $pickupRequest->created_at->format('d M Y, H:i') }}</p>
                        <p class="text-sm text-gray-400 mt-1">Bin reached {{ $bin->fill_level }}% fill — system auto-created pickup request</p>
                    </div>
                </div>

                {{-- Step 2: Claimed --}}
                <div class="relative mb-8">
                    @if ($pickupRequest->claimed_at)
                        <div class="absolute -left-10 w-6 h-6 rounded-full bg-blue-100 border-2 border-blue-400 flex items-center justify-center">
                            <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Claimed by {{ $pickupRequest->claimedBy->name }}</p>
                            <p class="text-sm text-gray-500 mt-0.5">{{ $pickupRequest->claimed_at->format('d M Y, H:i') }}</p>
                            <p class="text-sm text-gray-400 mt-1">
                                Response time: {{ $pickupRequest->created_at->diffForHumans($pickupRequest->claimed_at, true) }}
                            </p>
                        </div>
                    @else
                        <div class="absolute -left-10 w-6 h-6 rounded-full bg-gray-100 border-2 border-gray-300 border-dashed flex items-center justify-center">
                            <div class="w-2 h-2 rounded-full bg-gray-300"></div>
                        </div>
                        <div>
                            <p class="font-medium text-gray-400">Waiting for a collector to claim</p>
                            <p class="text-sm text-gray-400 mt-0.5">Pending for {{ $pickupRequest->created_at->diffForHumans(short: true) }}</p>
                        </div>
                    @endif
                </div>

                {{-- Step 3: Completed --}}
                <div class="relative">
                    @if ($pickupRequest->completed_at)
                        <div class="absolute -left-10 w-6 h-6 rounded-full bg-emerald-100 border-2 border-emerald-400 flex items-center justify-center">
                            <x-heroicon-s-check class="w-3 h-3 text-emerald-600" />
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Pickup Completed</p>
                            <p class="text-sm text-gray-500 mt-0.5">{{ $pickupRequest->completed_at->format('d M Y, H:i') }}</p>
                            <p class="text-sm text-gray-400 mt-1">Bin fill level reset to 0%</p>
                        </div>
                    @else
                        <div class="absolute -left-10 w-6 h-6 rounded-full bg-gray-100 border-2 border-gray-300 border-dashed flex items-center justify-center">
                            <div class="w-2 h-2 rounded-full bg-gray-300"></div>
                        </div>
                        <div>
                            <p class="font-medium text-gray-400">Not yet completed</p>
                        </div>
                    @endif
                </div>
            </div>
        </x-card>

        {{-- Admin Actions --}}
        @if ($pickupRequest->isPending() || $pickupRequest->isClaimed())
            <x-card class="p-6">
                <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <x-heroicon-o-cog-6-tooth class="w-4 h-4" />
                    Actions
                </h2>
                <div class="flex items-center gap-3">
                    @if ($pickupRequest->isClaimed())
                        <form method="POST" action="{{ route('admin.pickup-requests.unclaim', $pickupRequest) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition-colors">
                                <x-heroicon-o-arrow-uturn-left class="w-4 h-4" />
                                Return to Queue
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.pickup-requests.cancel', $pickupRequest) }}" onsubmit="return confirm('Are you sure you want to cancel this pickup request?')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 transition-colors">
                            <x-heroicon-o-x-circle class="w-4 h-4" />
                            Cancel Pickup
                        </button>
                    </form>
                </div>
            </x-card>
        @endif

        {{-- Bin Info --}}
        <x-card class="p-6">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                <x-heroicon-o-archive-box class="w-4 h-4" />
                Bin Details
            </h2>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center">
                    <x-heroicon-s-archive-box class="w-6 h-6 text-gray-400" />
                </div>
                <div>
                    <a href="{{ route('admin.bins.show', $bin) }}" class="font-semibold text-emerald-600 hover:text-emerald-700 text-base">
                        {{ $bin->serial_number }}
                    </a>
                    @if ($outlet)
                        <p class="text-sm text-gray-500 flex items-center gap-1 mt-0.5">
                            <x-heroicon-o-map-pin class="w-3.5 h-3.5" />
                            {{ $outlet->name }} — {{ $outlet->address }}
                        </p>
                    @else
                        <p class="text-sm text-gray-400 mt-0.5">Not currently assigned to an outlet</p>
                    @endif
                </div>
            </div>
        </x-card>
    </div>
</x-layouts.admin>

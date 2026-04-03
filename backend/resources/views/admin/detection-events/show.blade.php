<x-layouts.admin :title="'Detection #' . $detectionEvent->id">
    <x-slot:back>
        <x-back-button href="{{ route('admin.detection-events.index') }}" />
    </x-slot:back>

    <x-slot:header>
        Detection #{{ $detectionEvent->id }}
    </x-slot:header>

    @php
        $confidenceColor = match(true) {
            $detectionEvent->confidence >= 90 => 'bg-emerald-100 text-emerald-700',
            $detectionEvent->confidence >= 70 => 'bg-yellow-100 text-yellow-700',
            default => 'bg-gray-100 text-gray-600',
        };
    @endphp

    <div class="space-y-6">
        {{-- Detection Info --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-eye class="w-3.5 h-3.5" />
                    Detection Info
                </h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-xs text-gray-400">Waste Type</dt>
                        <dd class="text-lg font-semibold text-gray-900 flex items-center gap-2 mt-1">
                            <span class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center">
                                <x-heroicon-s-beaker class="w-5 h-5 {{ $detectionEvent->waste_type->iconColor() }}" />
                            </span>
                            {{ $detectionEvent->waste_type->label() }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Confidence</dt>
                        <dd class="flex items-center gap-3 mt-1">
                            <div class="flex-1 h-3 bg-gray-200 rounded-full overflow-hidden">
                                <div
                                    class="h-full {{ $detectionEvent->confidence >= 90 ? 'bg-emerald-500' : ($detectionEvent->confidence >= 70 ? 'bg-yellow-500' : 'bg-gray-400') }} rounded-full"
                                    style="width: {{ $detectionEvent->confidence }}%"
                                ></div>
                            </div>
                            <span class="text-lg font-semibold {{ $detectionEvent->confidence >= 90 ? 'text-emerald-600' : ($detectionEvent->confidence >= 70 ? 'text-yellow-600' : 'text-gray-600') }}">
                                {{ $detectionEvent->confidence }}%
                            </span>
                        </dd>
                    </div>
                    @if ($detectionEvent->weight_g)
                        <div>
                            <dt class="text-xs text-gray-400">Weight</dt>
                            <dd class="text-lg font-semibold text-amber-600 font-mono flex items-center gap-1.5">
                                <x-heroicon-o-scale class="w-4 h-4 text-amber-400" />
                                {{ $detectionEvent->weight_g >= 1000 ? number_format($detectionEvent->weight_g / 1000, 1) . 'kg' : $detectionEvent->weight_g . 'g' }}
                            </dd>
                        </div>
                    @endif
                    @if ($detectionEvent->waste_type?->isCup())
                        <div>
                            <dt class="text-xs text-gray-400">Detected Cup Brand</dt>
                            <dd class="flex items-center gap-2 mt-1">
                                @if ($detectionEvent->detectedBrand)
                                    @php
                                        $binBrand = $detectionEvent->bin->currentAssignment?->outlet?->brand;
                                        $isMatch = $binBrand && $binBrand->id === $detectionEvent->detectedBrand->id;
                                        $isCompetitor = $binBrand && !$isMatch;
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-sm font-medium {{ $isMatch ? 'bg-emerald-100 text-emerald-700' : ($isCompetitor ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                                        @if ($detectedBrandLogoPath = $detectionEvent->detectedBrand->logo_path)
                                            <img src="{{ asset('storage/' . $detectedBrandLogoPath) }}" alt="" class="w-4 h-4 rounded-full object-cover">
                                        @else
                                            <x-heroicon-o-building-storefront class="w-4 h-4" />
                                        @endif
                                        {{ $detectionEvent->detectedBrand->name }}
                                        @if ($isMatch)
                                            <x-heroicon-s-check-circle class="w-3.5 h-3.5 text-emerald-500" />
                                        @elseif ($isCompetitor)
                                            <x-heroicon-s-exclamation-triangle class="w-3.5 h-3.5 text-red-500" />
                                        @endif
                                    </span>
                                    @if ($isMatch)
                                        <span class="text-xs text-emerald-600">Brand match — full bonus</span>
                                    @elseif ($isCompetitor)
                                        <span class="text-xs text-red-600">Competitor — 0.3× penalty</span>
                                    @endif
                                @else
                                    <span class="text-gray-400 text-sm">Not detected</span>
                                @endif
                            </dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-400">Detected At</dt>
                        <dd class="text-gray-900 flex items-center gap-1.5">
                            <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                            {{ $detectionEvent->detected_at->format('d F Y, H:i:s') }}
                            <span class="text-gray-400 text-sm">({{ $detectionEvent->detected_at->diffForHumans() }})</span>
                        </dd>
                    </div>
                </dl>
            </div>
        </x-card>

        {{-- Bin Info --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-archive-box class="w-3.5 h-3.5" />
                    Bin Info
                </h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-gray-400">Bin</dt>
                        <dd>
                            <a href="{{ route('admin.bins.show', $detectionEvent->bin) }}" class="text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center gap-1.5">
                                <x-heroicon-o-archive-box class="w-4 h-4" />
                                {{ $detectionEvent->bin->serial_number }}
                            </a>
                        </dd>
                    </div>
                    @if ($detectionEvent->bin->currentAssignment)
                        <div>
                            <dt class="text-xs text-gray-400">Current Outlet</dt>
                            <dd>
                                <a href="{{ route('admin.outlets.show', $detectionEvent->bin->currentAssignment->outlet) }}" class="text-emerald-600 hover:text-emerald-700 inline-flex items-center gap-1.5">
                                    <x-heroicon-o-building-storefront class="w-4 h-4" />
                                    {{ $detectionEvent->bin->currentAssignment->outlet->name }}
                                </a>
                            </dd>
                        </div>
                    @else
                        <div>
                            <dt class="text-xs text-gray-400">Current Outlet</dt>
                            <dd class="text-gray-400">Bin is not currently assigned</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-400">Current Fill Level</dt>
                        <dd class="flex items-center gap-2">
                            @php
                                $fillColor = match(true) {
                                    $detectionEvent->bin->fill_level >= 80 => 'bg-red-500',
                                    $detectionEvent->bin->fill_level >= 50 => 'bg-yellow-500',
                                    default => 'bg-emerald-500',
                                };
                            @endphp
                            <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full {{ $fillColor }} rounded-full" style="width: {{ $detectionEvent->bin->fill_level }}%"></div>
                            </div>
                            <span class="text-sm text-gray-600">{{ $detectionEvent->bin->fill_level }}%</span>
                        </dd>
                    </div>
                </dl>
            </div>
        </x-card>

        {{-- Image --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-camera class="w-3.5 h-3.5" />
                    Image
                </h2>
                @if ($detectionEvent->image_path && Storage::disk('public')->exists($detectionEvent->image_path))
                    <div class="mt-4 rounded-xl overflow-hidden bg-gray-100">
                        <img
                            src="{{ asset('storage/' . $detectionEvent->image_path) }}"
                            alt="Detection image"
                            class="w-full h-auto"
                        >
                    </div>
                @else
                    <div class="mt-4 rounded-xl bg-gray-50 p-8 text-center">
                        <x-heroicon-o-photo class="w-10 h-10 text-gray-300 mx-auto mb-2" />
                        <p class="text-gray-400 text-sm">No image available for this detection</p>
                    </div>
                @endif
            </div>
        </x-card>

        {{-- Navigation --}}
        <div class="flex justify-between">
            <a
                href="{{ route('admin.detection-events.index', ['bin' => $detectionEvent->bin_id]) }}"
                class="text-sm text-emerald-600 hover:text-emerald-700 inline-flex items-center gap-1"
            >
                <x-heroicon-o-arrow-left class="w-3.5 h-3.5" />
                More detections from this bin
            </a>
        </div>
    </div>
</x-layouts.admin>

<x-layouts.admin :title="'Session #' . $binSession->id">
    <x-slot:back>
        <x-back-button href="{{ route('admin.bin-sessions.index') }}" />
    </x-slot:back>

    <x-slot:header>
        Session #{{ $binSession->id }}
    </x-slot:header>

    @php
        $statusColor = match($binSession->status) {
            'completed' => 'bg-emerald-100 text-emerald-700',
            'active' => 'bg-blue-100 text-blue-700',
            'expired' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-600',
        };
    @endphp

    <div class="space-y-6">
        {{-- Session Info --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-clock class="w-3.5 h-3.5" />
                    Session Info
                </h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-xs text-gray-400">Status</dt>
                        <dd class="mt-1">
                            <span class="text-sm font-medium rounded-full px-2.5 py-1 {{ $statusColor }}">
                                {{ ucfirst($binSession->status) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Bin</dt>
                        <dd>
                            <a href="{{ route('admin.bins.show', $binSession->bin) }}" class="text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center gap-1.5">
                                <x-heroicon-o-archive-box class="w-4 h-4" />
                                {{ $binSession->bin->serial_number }}
                            </a>
                        </dd>
                    </div>
                    @if ($binSession->bin->outlet)
                        <div>
                            <dt class="text-xs text-gray-400">Outlet</dt>
                            <dd>
                                <a href="{{ route('admin.outlets.show', $binSession->bin->outlet) }}" class="text-emerald-600 hover:text-emerald-700 inline-flex items-center gap-1.5">
                                    <x-heroicon-o-building-storefront class="w-4 h-4" />
                                    {{ $binSession->bin->outlet->name }}
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if ($binSession->user)
                        <div>
                            <dt class="text-xs text-gray-400">User</dt>
                            <dd class="text-gray-900 flex items-center gap-1.5">
                                <x-heroicon-o-user class="w-4 h-4 text-gray-400" />
                                {{ $binSession->user->name }}
                            </dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-400">Cup Rinsed</dt>
                        <dd class="text-gray-900">
                            {{ $binSession->cup_rinsed ? 'Yes' : 'No' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Started At</dt>
                        <dd class="text-gray-900 flex items-center gap-1.5">
                            <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                            {{ $binSession->started_at?->format('d F Y, H:i:s') ?? 'N/A' }}
                            @if ($binSession->started_at)
                                <span class="text-gray-400 text-sm">({{ $binSession->started_at->diffForHumans() }})</span>
                            @endif
                        </dd>
                    </div>
                    @if ($binSession->ended_at)
                        <div>
                            <dt class="text-xs text-gray-400">Ended At</dt>
                            <dd class="text-gray-900 flex items-center gap-1.5">
                                <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                                {{ $binSession->ended_at->format('d F Y, H:i:s') }}
                                <span class="text-gray-400 text-sm">({{ $binSession->ended_at->diffForHumans() }})</span>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </x-card>

        {{-- Detection Events --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-eye class="w-3.5 h-3.5" />
                    Detection Events ({{ $binSession->detectionEvents->count() }})
                </h2>
                @if ($binSession->detectionEvents->isEmpty())
                    <p class="text-gray-400 text-sm mt-2">No detection events in this session.</p>
                @else
                    <div class="space-y-3 mt-3">
                        @foreach ($binSession->detectionEvents as $event)
                            @php
                                $confidenceColor = match(true) {
                                    $event->confidence >= 90 => 'bg-emerald-100 text-emerald-700',
                                    $event->confidence >= 70 => 'bg-yellow-100 text-yellow-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <a href="{{ route('admin.detection-events.show', $event) }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-s-beaker class="w-4 h-4 {{ $event->waste_type?->iconColor() ?? 'text-gray-400' }}" />
                                    <span class="text-sm font-medium text-gray-900">{{ $event->waste_type?->label() ?? 'Unknown' }}</span>
                                    @if ($event->detectedBrand)
                                        <span class="text-xs text-gray-500">{{ $event->detectedBrand->name }}</span>
                                    @endif
                                </div>
                                <span class="text-xs font-medium rounded-full px-2 py-0.5 {{ $confidenceColor }}">
                                    {{ $event->confidence }}%
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-card>

        {{-- Transactions --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-currency-dollar class="w-3.5 h-3.5" />
                    Transactions ({{ $binSession->recyclingTransactions->count() }})
                </h2>
                @if ($binSession->recyclingTransactions->isEmpty())
                    <p class="text-gray-400 text-sm mt-2">No transactions in this session.</p>
                @else
                    <div class="space-y-3 mt-3">
                        @foreach ($binSession->recyclingTransactions as $txn)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">{{ ucfirst($txn->type) }}</span>
                                    @if ($txn->description)
                                        <p class="text-xs text-gray-500">{{ $txn->description }}</p>
                                    @endif
                                </div>
                                <span class="text-sm font-semibold {{ $txn->points >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $txn->points >= 0 ? '+' : '' }}{{ $txn->points }} pts
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-card>

        {{-- Navigation --}}
        <div class="flex justify-between">
            <a
                href="{{ route('admin.bin-sessions.index') }}"
                class="text-sm text-emerald-600 hover:text-emerald-700 inline-flex items-center gap-1"
            >
                <x-heroicon-o-arrow-left class="w-3.5 h-3.5" />
                Back to all sessions
            </a>
        </div>
    </div>
</x-layouts.admin>

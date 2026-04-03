<x-layouts.admin title="Bin Sessions">
    <x-slot:header>
        Bin Sessions
    </x-slot:header>

    @if ($sessions->isEmpty())
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-clock class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-400">No bin sessions yet</p>
            <p class="text-sm text-gray-400 mt-1">Sessions appear when users scan and interact with bins.</p>
        </x-card>
    @else
        <div class="space-y-3">
            @foreach ($sessions as $session)
                @php
                    $statusColor = match($session->status) {
                        'completed' => 'bg-emerald-100 text-emerald-700',
                        'active' => 'bg-blue-100 text-blue-700',
                        'expired' => 'bg-red-100 text-red-700',
                        default => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <x-card :href="route('admin.bin-sessions.show', $session)" :interactive="true" class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0 mt-0.5">
                                <x-heroicon-o-clock class="w-5 h-5 text-gray-400" />
                            </div>
                            <div class="min-w-0">
                                <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                                    #{{ $session->id }}
                                    <span class="text-xs font-medium rounded-full px-2 py-0.5 {{ $statusColor }}">
                                        {{ ucfirst($session->status) }}
                                    </span>
                                </h2>
                                <p class="text-sm text-gray-500 mt-0.5 flex items-center gap-1">
                                    <x-heroicon-o-archive-box class="w-3.5 h-3.5" />
                                    {{ $session->bin->serial_number ?? 'Unknown Bin' }}
                                    @if ($session->bin->outlet)
                                        <span class="text-gray-400">@</span>
                                        {{ $session->bin->outlet->name }}
                                    @endif
                                </p>
                                @if ($session->user)
                                    <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1">
                                        <x-heroicon-o-user class="w-3 h-3" />
                                        {{ $session->user->name }}
                                    </p>
                                @endif
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $session->started_at?->format('d M Y, H:i') ?? 'N/A' }}
                                    <span class="text-gray-300 mx-0.5">·</span>
                                    {{ $session->started_at?->diffForHumans() ?? '' }}
                                </p>
                            </div>
                        </div>
                        <div class="shrink-0 flex flex-col items-end gap-1.5">
                            <span class="text-xs font-medium text-gray-500">
                                {{ $session->detection_events_count }} {{ Str::plural('item', $session->detection_events_count) }}
                            </span>
                            @if ($session->recycling_transactions_sum_points)
                                <span class="text-xs font-semibold text-emerald-600">
                                    +{{ $session->recycling_transactions_sum_points }} pts
                                </span>
                            @endif
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $sessions->links() }}
        </div>
    @endif
</x-layouts.admin>

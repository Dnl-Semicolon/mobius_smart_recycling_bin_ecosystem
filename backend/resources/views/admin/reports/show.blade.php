<x-layouts.admin :title="'Report #' . $report->id">
    <x-slot:back>
        <x-back-button href="{{ route('admin.reports.index') }}" />
    </x-slot:back>

    <x-slot:header>
        Report #{{ $report->id }}
    </x-slot:header>

    @php
        $statusColor = match($report->status) {
            \App\Enums\ReportStatus::Open => 'bg-amber-100 text-amber-700',
            \App\Enums\ReportStatus::Investigating => 'bg-blue-100 text-blue-700',
            \App\Enums\ReportStatus::Resolved => 'bg-emerald-100 text-emerald-700',
            \App\Enums\ReportStatus::Closed => 'bg-gray-100 text-gray-600',
        };
    @endphp

    <div class="space-y-6">
        {{-- Status --}}
        <x-card>
            <div class="form-section">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium rounded-full px-2.5 py-1 {{ $statusColor }}">
                        {{ $report->status->label() }}
                    </span>
                    <span class="text-xs text-gray-400">
                        {{ $report->created_at->format('d F Y, H:i:s') }}
                        <span class="text-gray-300 mx-0.5">&middot;</span>
                        {{ $report->created_at->diffForHumans() }}
                    </span>
                </div>
            </div>
        </x-card>

        {{-- Report Details --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-flag class="w-3.5 h-3.5" />
                    Report Details
                </h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-xs text-gray-400">Type</dt>
                        <dd class="text-gray-900 font-medium mt-1">{{ $report->type->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Description</dt>
                        <dd class="text-gray-900 mt-1 whitespace-pre-line">{{ $report->description }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Reported By</dt>
                        <dd class="text-gray-900 mt-1">{{ $report->user->name }}</dd>
                    </div>
                </dl>
            </div>
        </x-card>

        {{-- Bin Info --}}
        @if ($report->bin)
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
                                <a href="{{ route('admin.bins.show', $report->bin) }}" class="text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center gap-1.5">
                                    <x-heroicon-o-archive-box class="w-4 h-4" />
                                    {{ $report->bin->serial_number }}
                                </a>
                            </dd>
                        </div>
                    </dl>
                </div>
            </x-card>
        @endif

        {{-- Resolution --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                    Resolution
                </h2>
                @if ($report->status === \App\Enums\ReportStatus::Resolved || $report->status === \App\Enums\ReportStatus::Closed)
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs text-gray-400">Resolution</dt>
                            <dd class="text-gray-900 mt-1 whitespace-pre-line">{{ $report->resolution }}</dd>
                        </div>
                        @if ($report->resolvedBy)
                            <div>
                                <dt class="text-xs text-gray-400">Resolved By</dt>
                                <dd class="text-gray-900 mt-1">{{ $report->resolvedBy->name }}</dd>
                            </div>
                        @endif
                        @if ($report->resolved_at)
                            <div>
                                <dt class="text-xs text-gray-400">Resolved At</dt>
                                <dd class="text-gray-900 mt-1 flex items-center gap-1.5">
                                    <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                                    {{ $report->resolved_at->format('d F Y, H:i:s') }}
                                    <span class="text-gray-400 text-sm">({{ $report->resolved_at->diffForHumans() }})</span>
                                </dd>
                            </div>
                        @endif
                    </dl>
                @else
                    <form method="POST" action="{{ route('admin.reports.resolve', $report) }}" class="space-y-3 mt-2">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Resolution</label>
                            <textarea
                                name="resolution"
                                rows="3"
                                required
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-400 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"
                                placeholder="Describe how the issue was resolved..."
                            >{{ old('resolution') }}</textarea>
                            @error('resolution')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <x-button type="submit" class="w-full justify-center">
                            <x-heroicon-o-check-circle class="w-4 h-4" />
                            Resolve Report
                        </x-button>
                    </form>
                @endif
            </div>
        </x-card>
    </div>
</x-layouts.admin>

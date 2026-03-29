<x-layouts.admin title="Reports">
    <x-slot:header>
        Reports
    </x-slot:header>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.reports.index') }}" class="mb-6">
        <div class="space-y-3">
            <div class="grid grid-cols-2 gap-2">
                <select
                    name="status"
                    class="rounded-xl border-gray-200 text-sm focus:border-emerald-400 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"
                >
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                <select
                    name="type"
                    class="rounded-xl border-gray-200 text-sm focus:border-emerald-400 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"
                >
                    <option value="">All Types</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected(request('type') === $type->value)>
                            {{ $type->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <x-button type="submit" variant="secondary" class="w-full justify-center">
                <x-heroicon-o-funnel class="w-4 h-4" />
                Filter
            </x-button>
        </div>
    </form>

    @if ($reports->isEmpty())
        {{-- Empty State --}}
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-flag class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            @if (request()->hasAny(['status', 'type']))
                <p class="text-gray-400">No reports match your filters</p>
                <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center gap-1.5 mt-4 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                    Clear filters
                </a>
            @else
                <p class="text-gray-400">No reports yet</p>
                <p class="text-sm text-gray-400 mt-1">Reports from users will appear here.</p>
            @endif
        </x-card>
    @else
        {{-- Report List --}}
        <div class="space-y-3">
            @foreach ($reports as $report)
                @php
                    $statusColor = match($report->status) {
                        \App\Enums\ReportStatus::Open => 'bg-amber-100 text-amber-700',
                        \App\Enums\ReportStatus::Investigating => 'bg-blue-100 text-blue-700',
                        \App\Enums\ReportStatus::Resolved => 'bg-emerald-100 text-emerald-700',
                        \App\Enums\ReportStatus::Closed => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <x-card :href="route('admin.reports.show', $report)" :interactive="true" class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0 mt-0.5">
                                <x-heroicon-s-flag class="w-5 h-5 text-gray-400" />
                            </div>
                            <div class="min-w-0">
                                <h2 class="font-semibold text-gray-900">
                                    {{ $report->type->label() }}
                                </h2>
                                <p class="text-sm text-gray-500 mt-0.5 flex items-center gap-1">
                                    @if ($report->bin)
                                        <x-heroicon-o-archive-box class="w-3.5 h-3.5" />
                                        {{ $report->bin->serial_number }}
                                        <span class="text-gray-400 mx-0.5">&middot;</span>
                                    @endif
                                    {{ $report->user->name }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $report->created_at->format('d M Y, H:i') }}
                                    <span class="text-gray-300 mx-0.5">&middot;</span>
                                    {{ $report->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <div class="shrink-0">
                            <span class="text-xs font-medium rounded-full px-2.5 py-1 {{ $statusColor }}">
                                {{ $report->status->label() }}
                            </span>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $reports->links() }}
        </div>
    @endif
</x-layouts.admin>

<x-layouts.admin title="Notifications">
    <x-slot:header>
        Notifications
    </x-slot:header>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.notifications.index') }}" class="mb-6">
        <div class="space-y-3">
            <select
                name="type"
                class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-400 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"
            >
                <option value="">All Types</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(request('type') === $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </select>
            <x-button type="submit" variant="secondary" class="w-full justify-center">
                <x-heroicon-o-funnel class="w-4 h-4" />
                Filter
            </x-button>
        </div>
    </form>

    @if ($notifications->isEmpty())
        {{-- Empty State --}}
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-bell class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            @if (request()->hasAny(['type']))
                <p class="text-gray-400">No notifications match your filter</p>
                <a href="{{ route('admin.notifications.index') }}" class="inline-flex items-center gap-1.5 mt-4 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                    Clear filter
                </a>
            @else
                <p class="text-gray-400">No notifications yet</p>
                <p class="text-sm text-gray-400 mt-1">User notifications will appear here.</p>
            @endif
        </x-card>
    @else
        {{-- Notification List --}}
        <div class="space-y-3">
            @foreach ($notifications as $notification)
                <x-card class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl {{ $notification->read_at ? 'bg-gray-50' : 'bg-emerald-50' }} flex items-center justify-center shrink-0 mt-0.5">
                                <x-heroicon-s-bell class="w-5 h-5 {{ $notification->read_at ? 'text-gray-400' : 'text-emerald-500' }}" />
                            </div>
                            <div class="min-w-0">
                                <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                                    {{ $notification->title }}
                                    @unless ($notification->read_at)
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                    @endunless
                                </h2>
                                <p class="text-sm text-gray-600 mt-0.5">{{ $notification->body }}</p>
                                <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
                                    <x-heroicon-o-user class="w-3.5 h-3.5" />
                                    {{ $notification->user->name }}
                                    <span class="text-gray-300 mx-0.5">&middot;</span>
                                    {{ $notification->type->label() }}
                                    <span class="text-gray-300 mx-0.5">&middot;</span>
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</x-layouts.admin>

<x-layouts.admin title="Users">
    <x-slot:header>
        Users
    </x-slot:header>

    {{-- Page header with action --}}
    <div class="flex items-center justify-between mb-4">
        <div></div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.2),0_1px_2px_rgba(0,0,0,0.15)] hover:bg-emerald-700 active:bg-emerald-800 active:shadow-[inset_0_1px_3px_rgba(0,0,0,0.2)] transition-all duration-100">
            <x-heroicon-o-user-plus class="w-4 h-4 opacity-80" />
            New
        </a>
    </div>

    {{-- Search & Filters --}}
    <form id="userFilterForm" method="GET" action="{{ route('admin.users.index') }}" class="mb-6 space-y-3">
        {{-- Search bar --}}
        <div class="relative" x-data="{ focused: false }">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
            </div>
            <input
                id="userSearchInput"
                name="search"
                type="text"
                placeholder="Search name or email..."
                value="{{ request('search') }}"
                autocomplete="off"
                x-on:focus="focused = true"
                x-on:blur="focused = false"
                class="w-full rounded-xl border border-gray-200/80 bg-white/60 pl-11 pr-20 py-3 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition-all duration-150 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 focus:shadow-emerald-500/5"
            >
            <div class="absolute inset-y-0 right-3 flex items-center gap-2">
                <kbd
                    x-show="!focused"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-90"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-90"
                    class="hidden sm:inline-flex items-center gap-0.5 rounded-md border border-gray-200 bg-gray-50 px-1.5 py-0.5 text-[11px] font-medium text-gray-400 shadow-sm"
                    title="Press / to search"
                >/</kbd>
                <button type="submit" class="rounded-lg bg-emerald-600 p-1.5 text-white shadow-sm hover:bg-emerald-700 active:bg-emerald-800 transition-colors cursor-pointer" title="Search">
                    <x-heroicon-s-arrow-right class="w-4 h-4" />
                </button>
            </div>
        </div>

        {{-- Filter controls --}}
        <div class="flex flex-wrap items-center gap-3">
            <div class="min-w-[160px]">
                <x-dropdown.select
                    name="role"
                    placeholder="All Roles"
                    :options="collect($roles)->mapWithKeys(fn($r) => [$r->value => $r->label()])->toArray()"
                    :selected="request('role')"
                />
            </div>
            @if (request()->hasAny(['search', 'role']))
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-red-500 transition-colors ml-1">
                    <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                    Clear filters
                </a>
            @endif
        </div>
    </form>

    @once
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('userSearchInput');
        if (!searchInput) return;

        document.addEventListener('keydown', function (e) {
            if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName) && !document.activeElement.isContentEditable) {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
            if (e.key === 'Escape' && document.activeElement === searchInput) {
                searchInput.blur();
            }
        });

        // Auto-submit on any dropdown change within a form
        document.addEventListener('change', function (e) {
            if (e.target.matches('[data-dropdown-value]')) {
                var form = e.target.closest('form');
                if (form) form.submit();
            }
        });
    });
    </script>
    @endonce

    @php
        $roleColors = [
            'admin' => 'bg-violet-50 text-violet-700 border-violet-200',
            'collector' => 'bg-blue-50 text-blue-700 border-blue-200',
            'public_user' => 'bg-gray-50 text-gray-600 border-gray-200',
            'store_owner' => 'bg-amber-50 text-amber-700 border-amber-200',
            'agency_admin' => 'bg-teal-50 text-teal-700 border-teal-200',
        ];
    @endphp

    {{-- View toggle --}}
    <div x-data="{ view: localStorage.getItem('user-view') || 'row' }" x-init="$watch('view', v => localStorage.setItem('user-view', v))">
        <div class="flex items-center justify-end mb-4">
            <div class="inline-flex rounded-lg bg-gray-100 p-0.5 ring-1 ring-gray-200/60 shrink-0">
                <button
                    @click="view = 'row'"
                    :class="view === 'row' ? 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200/60' : 'text-gray-400 hover:text-gray-600'"
                    class="relative inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-semibold transition-all duration-150 cursor-pointer"
                    title="List view"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                        <line x1="1" y1="3" x2="15" y2="3" /><line x1="1" y1="8" x2="15" y2="8" /><line x1="1" y1="13" x2="15" y2="13" />
                    </svg>
                    Rows
                </button>
                <button
                    @click="view = 'grid'"
                    :class="view === 'grid' ? 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200/60' : 'text-gray-400 hover:text-gray-600'"
                    class="relative inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-semibold transition-all duration-150 cursor-pointer"
                    title="Grid view"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="1" width="6" height="6" rx="1" /><rect x="9" y="1" width="6" height="6" rx="1" /><rect x="1" y="9" width="6" height="6" rx="1" /><rect x="9" y="9" width="6" height="6" rx="1" />
                    </svg>
                    Grid
                </button>
            </div>
        </div>

    @if ($users->isEmpty())
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-users class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            @if (request()->hasAny(['search', 'role']))
                <p class="text-gray-400">No users match your filters</p>
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 mt-4 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                    Clear filters
                </a>
            @else
                <p class="text-gray-400 mb-6">No users yet</p>
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.2),0_1px_2px_rgba(0,0,0,0.15)] hover:bg-emerald-700 active:bg-emerald-800 active:shadow-[inset_0_1px_3px_rgba(0,0,0,0.2)] transition-all duration-100">
                    <x-heroicon-o-plus class="w-4 h-4 opacity-80" />
                    Create your first user
                </a>
            @endif
        </x-card>
    @else
        {{-- Row view --}}
        <div x-show="view === 'row'" class="space-y-3">
            @foreach ($users as $user)
                @php $isSelf = $user->id === auth()->id(); @endphp
                <x-card :href="$isSelf ? route('admin.profile.edit') : route('admin.users.edit', $user)" :interactive="true" class="{{ $isSelf ? 'p-5 ring-1 ring-emerald-200 border-emerald-200/60 bg-emerald-50/30' : 'p-5' }}">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="relative w-11 h-11 rounded-full overflow-hidden shrink-0">
                                @if ($user->avatar_path)
                                    <img src="{{ Storage::url($user->avatar_path) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-emerald-100 flex items-center justify-center">
                                        <span class="text-sm font-semibold text-emerald-700">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(str_contains($user->name, ' ') ? explode(' ', $user->name)[1] : '', 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold text-gray-900 text-base truncate">{{ $user->name }}</p>
@if ($isSelf)
                                        <span class="text-[10px] font-semibold uppercase tracking-wide text-emerald-600 bg-emerald-100 rounded px-1.5 py-0.5 shrink-0">You</span>
                                    @endif
                                    @if ($user->username)
                                        <span class="text-sm text-gray-400 truncate hidden sm:inline">{{ '@' . $user->username }}</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 mt-0.5 truncate">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="shrink-0 flex flex-col items-end gap-2">
                            <div class="flex flex-wrap gap-1 justify-end">
                                @foreach ($user->getRolesArray() as $role)
                                    <span class="text-xs font-medium rounded-full px-2.5 py-0.5 border {{ $roleColors[$role] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                        {{ ucwords(str_replace('_', ' ', $role)) }}
                                    </span>
                                @endforeach
                            </div>
                            <span class="text-xs text-gray-400">Joined {{ $user->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        {{-- Grid view --}}
        <div x-show="view === 'grid'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($users as $user)
                @php $isSelf = $user->id === auth()->id(); @endphp
                <a href="{{ $isSelf ? route('admin.profile.edit') : route('admin.users.edit', $user) }}"
                   class="group block rounded-2xl backdrop-blur-2xl ring-1 ring-inset ring-white/80 shadow-sm hover:-translate-y-1 hover:shadow-[0_20px_40px_-12px_rgba(0,0,0,0.12)] hover:bg-white hover:border-gray-300/70 transition-all duration-300 ease-out {{ $isSelf ? 'bg-emerald-50/50 border border-emerald-200/60 ring-emerald-100' : 'bg-white/90 border border-gray-200/60' }}">
                    <div class="p-5">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-full overflow-hidden shrink-0 ring-[3px] {{ $isSelf ? 'ring-emerald-200' : 'ring-gray-100' }}">
                                @if ($user->avatar_path)
                                    <img src="{{ Storage::url($user->avatar_path) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-emerald-50 via-emerald-100 to-teal-100 flex items-center justify-center">
                                        <span class="text-lg font-bold text-emerald-600">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(str_contains($user->name, ' ') ? explode(' ', $user->name)[1] : '', 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold text-gray-900 truncate group-hover:text-emerald-700 transition-colors">{{ $user->name }}</p>
                                    @if ($isSelf)
                                        <span class="text-[10px] font-semibold uppercase tracking-wide text-emerald-600 bg-emerald-100 rounded px-1.5 py-0.5 shrink-0">You</span>
                                    @endif
                                </div>
                                @if ($user->username)
                                    <p class="text-xs text-gray-400 truncate">{{ '@' . $user->username }}</p>
                                @endif
                                <p class="text-sm text-gray-500 mt-0.5 truncate">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-200/50 flex items-center justify-between gap-2">
                            <div class="flex flex-wrap gap-1 min-w-0">
                                @foreach ($user->getRolesArray() as $role)
                                    <span class="text-[11px] font-medium rounded-full px-2 py-0.5 border {{ $roleColors[$role] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                        {{ ucwords(str_replace('_', ' ', $role)) }}
                                    </span>
                                @endforeach
                            </div>
                            <span class="text-[11px] text-gray-400 whitespace-nowrap shrink-0">{{ $user->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Pagination + Per page --}}
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="order-2 sm:order-1">
                {{ $users->links() }}
            </div>
            <form method="GET" action="{{ route('admin.users.index') }}" class="order-1 sm:order-2 flex items-center gap-2">
                @if (request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                @if (request('role'))
                    <input type="hidden" name="role" value="{{ request('role') }}">
                @endif
                <span class="text-xs text-gray-400 whitespace-nowrap">Show</span>
                <div class="min-w-[100px]">
                    <x-dropdown.select
                        name="per_page"
                        :options="['15' => '15', '25' => '25', '50' => '50', '100' => '100']"
                        :selected="(string) request('per_page', '15')"
                    />
                </div>
                <span class="text-xs text-gray-400 whitespace-nowrap">per page</span>
            </form>
        </div>
    @endif
    </div>
</x-layouts.admin>

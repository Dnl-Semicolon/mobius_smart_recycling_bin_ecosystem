<x-layouts.admin title="Users">
    <x-slot:header>
        Users
    </x-slot:header>

    <x-slot:actions>
        <x-button href="{{ route('admin.users.create') }}">
            <x-heroicon-o-plus class="w-4 h-4" />
            Add
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

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-6">
        <div class="flex gap-2">
            <x-input
                name="search"
                type="text"
                placeholder="Search name or email..."
                :value="request('search')"
                class="flex-1"
            />
            <select
                name="role"
                class="rounded-xl border-gray-200 text-sm focus:border-emerald-400 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"
            >
                <option value="">All Roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}" @selected(request('role') === $role->value)>
                        {{ ucfirst(str_replace('_', ' ', $role->value)) }}
                    </option>
                @endforeach
            </select>
            <x-button type="submit" variant="secondary">
                <x-heroicon-o-funnel class="w-4 h-4" />
                Filter
            </x-button>
        </div>
    </form>

    @if ($users->isEmpty())
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-users class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-400 text-lg">No users found</p>
        </x-card>
    @else
        <div class="space-y-3">
            @foreach ($users as $user)
                @php
                    $roleColors = [
                        'admin' => 'bg-violet-100 text-violet-700',
                        'collector' => 'bg-blue-100 text-blue-700',
                        'public_user' => 'bg-gray-100 text-gray-600',
                    ];
                    $roleIcons = [
                        'admin' => 'shield-check',
                        'collector' => 'truck',
                        'public_user' => 'user',
                    ];
                @endphp
                <x-card :href="route('admin.users.edit', $user)" :interactive="true" class="p-5">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="w-11 h-11 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                                <x-heroicon-s-user class="w-6 h-6 text-emerald-600" />
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 text-base">{{ $user->name }}</p>
                                <p class="text-sm text-gray-500 mt-0.5">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="shrink-0 flex flex-col items-end gap-2">
                            <span class="text-xs font-semibold rounded-full px-3 py-1 {{ $roleColors[$user->role->value] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst(str_replace('_', ' ', $user->role->value)) }}
                            </span>
                            <span class="text-xs text-gray-400">
                                Joined {{ $user->created_at->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    @endif
</x-layouts.admin>

<x-layouts.admin :title="$organization->name">
    <x-slot:back>
        <x-back-button href="{{ route('admin.organizations.index') }}" />
    </x-slot:back>

    <x-slot:header>
        {{ $organization->name }}
    </x-slot:header>

    <div class="space-y-5">
        {{-- Organization info card --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 lg:p-6">
            <div class="flex flex-col sm:flex-row sm:items-start gap-5">
                <div class="shrink-0">
                    @if ($organization->logo_path)
                        <img src="{{ Storage::url($organization->logo_path) }}" alt="{{ $organization->name }}" class="w-16 h-16 rounded-xl object-cover ring-1 ring-black/5">
                    @else
                        <div class="w-16 h-16 rounded-xl flex items-center justify-center ring-1 ring-gray-200 bg-gray-50">
                            <x-heroicon-s-building-office-2 class="w-8 h-8 text-gray-400" />
                        </div>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2.5 mb-2">
                        @if ($organization->is_active)
                            <span class="text-xs font-semibold rounded-full px-2.5 py-1 bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200/60">Active</span>
                        @else
                            <span class="text-xs font-semibold rounded-full px-2.5 py-1 bg-gray-100 text-gray-600 ring-1 ring-gray-200/60">Inactive</span>
                        @endif
                    </div>

                    <h2 class="text-lg font-bold text-gray-900">{{ $organization->name }}</h2>

                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Type</p>
                            <p class="font-semibold text-gray-900 mt-0.5">{{ ucfirst(str_replace('_', ' ', $organization->type ?? 'N/A')) }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Subscription</p>
                            @if ($organization->subscription?->plan)
                                <p class="font-semibold text-gray-900 mt-0.5">{{ $organization->subscription->plan->name }}</p>
                                <p class="text-xs text-gray-500">{{ ucfirst($organization->subscription->status ?? 'N/A') }}</p>
                            @else
                                <p class="text-gray-400 mt-0.5">No subscription</p>
                            @endif
                        </div>
                        @if ($organization->website)
                            <div>
                                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Website</p>
                                <a href="{{ $organization->website }}" target="_blank" rel="noopener" class="font-semibold text-emerald-600 hover:text-emerald-700 mt-0.5 block truncate">
                                    {{ $organization->website }}
                                </a>
                            </div>
                        @endif
                        @if ($organization->description)
                            <div class="sm:col-span-2">
                                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Description</p>
                                <p class="text-gray-700 mt-0.5">{{ $organization->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            @php
                $totalOutlets = $organization->brands->sum(fn ($b) => $b->outlets->count());
                $statCards = [
                    ['label' => 'Brands', 'value' => $organization->brands->count(), 'icon' => 'heroicon-o-building-storefront', 'color' => 'bg-blue-100 text-blue-600 ring-blue-200/60'],
                    ['label' => 'Outlets', 'value' => $totalOutlets, 'icon' => 'heroicon-o-map-pin', 'color' => 'bg-emerald-100 text-emerald-600 ring-emerald-200/60'],
                    ['label' => 'Users', 'value' => $organization->users->count(), 'icon' => 'heroicon-o-users', 'color' => 'bg-violet-100 text-violet-600 ring-violet-200/60'],
                ];
            @endphp

            @foreach ($statCards as $card)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="w-8 h-8 rounded-lg ring-1 flex items-center justify-center {{ $card['color'] }}">
                            <x-dynamic-component :component="$card['icon']" class="w-4 h-4" />
                        </div>
                        <p class="text-xs font-medium text-gray-500">{{ $card['label'] }}</p>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Brands section --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 lg:p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center ring-1 ring-blue-200/60">
                        <x-heroicon-s-building-storefront class="w-4 h-4 text-blue-600" />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Brands</h3>
                </div>
                <span class="text-xs font-semibold text-gray-400 bg-gray-100 rounded-full px-2 py-0.5">{{ $organization->brands->count() }}</span>
            </div>

            @if ($organization->brands->isEmpty())
                <div class="rounded-xl bg-gray-50 border border-gray-100 py-10 text-center">
                    <x-heroicon-o-building-storefront class="w-8 h-8 text-gray-300 mx-auto mb-2" />
                    <p class="text-sm text-gray-400 font-medium">No brands</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($organization->brands as $brand)
                        <div class="rounded-xl border border-gray-100 p-4 hover:bg-gray-50/50 transition-colors">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.brands.show', $brand) }}" class="font-semibold text-gray-900 hover:text-emerald-600 transition-colors">
                                        {{ $brand->name }}
                                    </a>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        {{ $brand->point_multiplier }}x multiplier
                                        @if ($brand->is_active)
                                            <span class="text-emerald-600">&middot; Active</span>
                                        @else
                                            <span class="text-gray-400">&middot; Inactive</span>
                                        @endif
                                    </p>
                                </div>
                                <span class="text-xs text-gray-400 flex items-center gap-1 shrink-0">
                                    <x-heroicon-o-map-pin class="w-3 h-3" />
                                    {{ $brand->outlets->count() }} {{ Str::plural('outlet', $brand->outlets->count()) }}
                                </span>
                            </div>

                            {{-- Outlets under this brand --}}
                            @if ($brand->outlets->isNotEmpty())
                                <div class="mt-2 flex items-center gap-1.5 flex-wrap">
                                    <x-heroicon-o-building-storefront class="w-3 h-3 text-gray-400 shrink-0" />
                                    @foreach ($brand->outlets as $outlet)
                                        <a href="{{ route('admin.outlets.show', $outlet) }}" class="text-xs text-gray-500 bg-gray-100 rounded-full px-2 py-0.5 hover:bg-gray-200 transition-colors">
                                            {{ $outlet->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Users section --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 lg:p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center ring-1 ring-violet-200/60">
                        <x-heroicon-s-users class="w-4 h-4 text-violet-600" />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Users</h3>
                </div>
                <span class="text-xs font-semibold text-gray-400 bg-gray-100 rounded-full px-2 py-0.5">{{ $organization->users->count() }}</span>
            </div>

            @if ($organization->users->isEmpty())
                <div class="rounded-xl bg-gray-50 border border-gray-100 py-10 text-center">
                    <x-heroicon-o-users class="w-8 h-8 text-gray-300 mx-auto mb-2" />
                    <p class="text-sm text-gray-400 font-medium">No users</p>
                </div>
            @else
                <div class="overflow-hidden rounded-lg border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/60">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($organization->users as $user)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $user->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $user->email }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 text-center">{{ ucfirst(str_replace('_', ' ', $user->role ?? 'N/A')) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-400 text-center">{{ $user->created_at?->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>

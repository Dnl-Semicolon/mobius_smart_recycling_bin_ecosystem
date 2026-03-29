<x-layouts.app title="Collector Dashboard — Mobius">
    <div class="min-h-screen bg-gray-50">
        {{-- Header --}}
        <header class="bg-white border-b border-gray-200/60">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/mobius-icon.png') }}" alt="Mobius" class="w-8 h-8 object-contain">
                    <img src="{{ asset('images/mobius-wordmark.png') }}" alt="Mobius" class="h-5 object-contain">
                    <span class="text-xs text-gray-400 border-l border-gray-200 pl-3 ml-1">Collector</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('collector.profile.edit') }}" class="text-sm text-gray-600 hover:text-emerald-600 transition-colors">{{ auth()->user()->name }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-400 hover:text-red-600 transition-colors">Sign out</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-6 flex items-center gap-2 rounded-xl bg-emerald-50 border border-emerald-200/60 px-4 py-3 text-sm text-emerald-700">
                    <x-heroicon-s-check-circle class="w-5 h-5 shrink-0" />
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-6 flex items-center gap-2 rounded-xl bg-red-50 border border-red-200/60 px-4 py-3 text-sm text-red-700">
                    <x-heroicon-s-exclamation-circle class="w-5 h-5 shrink-0" />
                    {{ session('error') }}
                </div>
            @endif

            {{-- Stats Bar --}}
            <section class="mb-10">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="bg-white rounded-xl border border-gray-200/60 p-4">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Completed</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_completed'] }}</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200/60 p-4">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">This Week</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['this_week'] }}</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200/60 p-4">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Time</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['avg_minutes'] !== null ? $stats['avg_minutes'] . ' min' : '—' }}</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200/60 p-4">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Active Now</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['active_now'] }}</p>
                    </div>
                </div>
            </section>

            {{-- Section 1: Available Pickups --}}
            <section class="mb-10">
                <div class="flex items-center gap-2 mb-4">
                    <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-amber-500" />
                    <h2 class="text-lg font-semibold text-gray-900">Available Pickups</h2>
                    <span class="text-xs bg-amber-100 text-amber-700 font-medium px-2 py-0.5 rounded-full">{{ $availablePickups->count() }}</span>
                </div>

                @if ($availablePickups->isEmpty())
                    <div class="bg-white rounded-xl border border-gray-200/60 p-8 text-center">
                        <x-heroicon-o-check-circle class="w-10 h-10 text-emerald-400 mx-auto mb-2" />
                        <p class="text-sm text-gray-500">No pickups available right now. Check back later!</p>
                    </div>
                @else
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($availablePickups as $pickup)
                            @php
                                $bin = $pickup->bin;
                                $outlet = $bin->currentAssignment?->outlet;
                            @endphp
                            <div class="bg-white rounded-xl border border-gray-200/60 p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="font-medium text-gray-900">{{ $bin->serial_number }}</span>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $bin->fill_level >= 90 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $bin->fill_level }}%
                                    </span>
                                </div>
                                {{-- Fill bar --}}
                                <div class="w-full h-2 bg-gray-100 rounded-full mb-3">
                                    <div class="h-2 rounded-full {{ $bin->fill_level >= 90 ? 'bg-red-500' : 'bg-amber-500' }}" style="width: {{ $bin->fill_level }}%"></div>
                                </div>
                                @if ($outlet)
                                    <p class="text-xs text-gray-500 flex items-center gap-1 mb-1">
                                        <x-heroicon-o-map-pin class="w-3.5 h-3.5" />
                                        {{ $outlet->name }}
                                    </p>
                                    @if ($outlet->address)
                                        <p class="text-xs text-gray-400 ml-5 mb-2">{{ $outlet->address }}</p>
                                    @endif
                                @endif
                                <p class="text-xs text-gray-400 mb-3">
                                    Requested {{ $pickup->created_at->diffForHumans() }}
                                </p>
                                <form method="POST" action="{{ route('collector.pickups.claim', $pickup) }}">
                                    @csrf
                                    <button type="submit" class="w-full text-center text-sm font-medium bg-emerald-600 text-white rounded-lg px-3 py-2 hover:bg-emerald-700 transition-colors flex items-center justify-center gap-1.5">
                                        <x-heroicon-o-hand-raised class="w-4 h-4" />
                                        Claim This Pickup
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Section 2: My Active Pickups --}}
            <section class="mb-10">
                <div class="flex items-center gap-2 mb-4">
                    <x-heroicon-s-truck class="w-5 h-5 text-blue-500" />
                    <h2 class="text-lg font-semibold text-gray-900">My Active Pickups</h2>
                    <span class="text-xs bg-blue-100 text-blue-700 font-medium px-2 py-0.5 rounded-full">{{ $myActivePickups->count() }}</span>
                </div>

                @if ($myActivePickups->isEmpty())
                    <div class="bg-white rounded-xl border border-gray-200/60 p-8 text-center">
                        <x-heroicon-o-truck class="w-10 h-10 text-gray-300 mx-auto mb-2" />
                        <p class="text-sm text-gray-500">You haven't claimed any pickups yet. Browse available pickups above!</p>
                    </div>
                @else
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($myActivePickups as $pickup)
                            @php
                                $bin = $pickup->bin;
                                $outlet = $bin->currentAssignment?->outlet;
                            @endphp
                            <div class="bg-white rounded-xl border-2 border-blue-200 p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="font-medium text-gray-900">{{ $bin->serial_number }}</span>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                        Claimed
                                    </span>
                                </div>
                                {{-- Fill bar --}}
                                <div class="w-full h-2 bg-gray-100 rounded-full mb-3">
                                    <div class="h-2 rounded-full bg-blue-500" style="width: {{ $bin->fill_level }}%"></div>
                                </div>
                                @if ($outlet)
                                    <p class="text-xs text-gray-500 flex items-center gap-1 mb-1">
                                        <x-heroicon-o-map-pin class="w-3.5 h-3.5" />
                                        {{ $outlet->name }}
                                    </p>
                                    @if ($outlet->address)
                                        <p class="text-xs text-gray-400 ml-5 mb-2">{{ $outlet->address }}</p>
                                    @endif
                                @endif
                                <p class="text-xs text-gray-400 mb-3">
                                    Claimed {{ $pickup->claimed_at->diffForHumans() }}
                                </p>
                                <form method="POST" action="{{ route('collector.pickups.complete', $pickup) }}">
                                    @csrf
                                    <button type="submit" class="w-full text-center text-sm font-medium bg-blue-600 text-white rounded-lg px-3 py-2 hover:bg-blue-700 transition-colors flex items-center justify-center gap-1.5">
                                        <x-heroicon-o-check-circle class="w-4 h-4" />
                                        Mark as Completed
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Section 3: My History --}}
            <section x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-2 mb-4 group">
                    <x-heroicon-o-clock class="w-5 h-5 text-gray-400" />
                    <h2 class="text-lg font-semibold text-gray-900">My History</h2>
                    <span class="text-xs bg-gray-100 text-gray-600 font-medium px-2 py-0.5 rounded-full">{{ $myHistory->count() }}</span>
                    <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                </button>

                <div x-show="open" x-collapse>
                    @if ($myHistory->isEmpty())
                        <div class="bg-white rounded-xl border border-gray-200/60 p-8 text-center">
                            <p class="text-sm text-gray-500">No completed pickups yet.</p>
                        </div>
                    @else
                        <div class="bg-white rounded-xl border border-gray-200/60 overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50/80">
                                    <tr>
                                        <th class="text-left px-4 py-2.5 font-medium text-gray-500">Bin</th>
                                        <th class="text-left px-4 py-2.5 font-medium text-gray-500">Outlet</th>
                                        <th class="text-right px-4 py-2.5 font-medium text-gray-500">Completed</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($myHistory as $pickup)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-900">{{ $pickup->bin->serial_number }}</td>
                                            <td class="px-4 py-3 text-gray-600">{{ $pickup->bin->currentAssignment?->outlet?->name ?? '—' }}</td>
                                            <td class="px-4 py-3 text-right text-gray-500">{{ $pickup->completed_at->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>
        </main>
    </div>
</x-layouts.app>

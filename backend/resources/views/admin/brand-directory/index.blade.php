<x-layouts.admin title="Brand Directory">
    <x-slot:header>
        Brand Directory
    </x-slot:header>

    <x-slot:actions>
        <a href="{{ route('admin.brand-directory.create') }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 transition-colors">
            <x-heroicon-o-plus class="w-4 h-4" />
            Add Brand
        </a>
    </x-slot:actions>

    @php
        $currentSearch = request('search', '');
        $currentStatus = request('status', '');
        $currentPerPage = in_array(request('per_page'), ['18','30','60']) ? request('per_page') : '18';
    @endphp

    {{-- Search bar (own scope for keyboard shortcuts) --}}
    <div class="mb-4">
        <form method="GET" action="{{ route('admin.brand-directory.index') }}" class="flex gap-2">
            <input type="hidden" name="status" value="{{ $currentStatus }}">
            <input type="hidden" name="per_page" value="{{ $currentPerPage }}">
            <div class="relative flex-1"
                 x-data
                 x-on:keydown.slash.window.prevent="$refs.searchInput.focus()"
                 x-on:keydown.meta.k.window.prevent="$refs.searchInput.focus()">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                    type="search"
                    name="search"
                    value="{{ $currentSearch }}"
                    placeholder="Search brands..."
                    x-ref="searchInput"
                    x-on:keydown.escape="$el.blur()"
                    class="w-full rounded-xl border border-gray-200/80 bg-white/60 pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 focus:outline-none transition-all"
                    data-form-type="other"
                >
            </div>
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-gray-800 transition-colors shadow-sm">
                <x-heroicon-o-arrow-right class="w-4 h-4" />
            </button>
        </form>
    </div>

    {{-- Single x-data wraps toggle + content so they share state --}}
    <div x-data="{ view: localStorage.getItem('brand-directory-view') || 'grid' }"
         x-effect="localStorage.setItem('brand-directory-view', view)"
         x-cloak>

        {{-- Filter pills + view toggle --}}
        <div class="flex items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-2 flex-wrap">
                @php
                    $filters = [
                        '' => ['label' => 'All', 'count' => $statusCounts['all'], 'dot' => 'bg-gray-400', 'on' => 'bg-gray-900 text-white border-gray-900'],
                        'available' => ['label' => 'Available', 'count' => $statusCounts['available'], 'dot' => 'bg-emerald-500', 'on' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                        'claimed' => ['label' => 'Claimed', 'count' => $statusCounts['claimed'], 'dot' => 'bg-blue-500', 'on' => 'bg-blue-50 text-blue-700 border-blue-200'],
                    ];
                @endphp

                @foreach ($filters as $value => $f)
                    @php $isActive = $currentStatus === (string) $value; @endphp
                    <a href="{{ route('admin.brand-directory.index', array_filter(['status' => $value, 'search' => $currentSearch, 'per_page' => $currentPerPage])) }}"
                       class="inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-sm font-medium border transition-all
                           {{ $isActive ? $f['on'] . ' shadow-sm' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }}">
                        @if ($value !== '')
                            <span class="w-2 h-2 rounded-full {{ $f['dot'] }} {{ $isActive ? 'ring-4 ring-current/10' : '' }}"></span>
                        @endif
                        {{ $f['label'] }}
                        <span class="text-[11px] font-semibold rounded-full min-w-[20px] px-1.5 py-0.5 text-center leading-none {{ $isActive ? 'bg-current/10' : 'bg-gray-100 text-gray-500' }}">{{ $f['count'] }}</span>
                    </a>
                @endforeach

                @if ($currentSearch || $currentStatus)
                    <a href="{{ route('admin.brand-directory.index') }}"
                       class="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-red-500 ml-1 transition-colors">
                        <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                        Clear
                    </a>
                @endif
            </div>

            {{-- View toggle --}}
            <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-0.5">
                <button @click="view = 'grid'" :class="view === 'grid' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-400 hover:text-gray-600'" class="p-1.5 rounded-md transition-all cursor-pointer" title="Grid view">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z" />
                    </svg>
                </button>
                <button @click="view = 'row'" :class="view === 'row' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-400 hover:text-gray-600'" class="p-1.5 rounded-md transition-all cursor-pointer" title="List view">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                    </svg>
                </button>
            </div>
        </div>

        @if ($brands->isEmpty())
            <x-card variant="subtle" class="text-center py-16">
                <x-heroicon-o-rectangle-stack class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                @if ($currentSearch || $currentStatus)
                    <p class="text-gray-400">No brands match your filters</p>
                    <a href="{{ route('admin.brand-directory.index') }}" class="inline-flex items-center gap-1.5 mt-4 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                        Clear filters
                    </a>
                @else
                    <p class="text-gray-400">No brands in directory yet</p>
                    <a href="{{ route('admin.brand-directory.create') }}" class="inline-flex items-center gap-1.5 mt-4 text-sm font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Add your first brand
                    </a>
                @endif
            </x-card>
        @else
            {{-- GRID --}}
            <div x-show="view === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($brands as $brand)
                    <a href="{{ route('admin.brand-directory.edit', $brand) }}" class="group block h-full">
                        <div class="relative bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 h-full flex flex-col">
                            {{-- Color accent strip --}}
                            <div class="h-1.5 shrink-0" style="background: {{ $brand->primary_color ?? '#D1D5DB' }}"></div>

                            <div class="p-5 flex flex-col flex-1">
                                {{-- Logo / Avatar --}}
                                <div class="flex items-start justify-between mb-4">
                                    <div class="w-14 h-14 rounded-xl overflow-hidden shadow-sm ring-1 ring-black/5 flex items-center justify-center shrink-0"
                                         style="background: {{ $brand->logo_path ? '#ffffff' : ($brand->primary_color ?? '#6B7280') }}">
                                        @if ($brand->logo_path)
                                            <img src="{{ Storage::url($brand->logo_path) }}" alt="{{ $brand->name }}" class="w-full h-full object-contain p-1.5">
                                        @else
                                            <span class="text-white text-lg font-bold tracking-tight">{{ strtoupper(substr($brand->name, 0, 2)) }}</span>
                                        @endif
                                    </div>
                                    @if ($brand->user_id)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-600 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Claimed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-600 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Available
                                        </span>
                                    @endif
                                </div>

                                {{-- Info — fixed-height description zone --}}
                                <h3 class="text-sm font-bold text-gray-900 group-hover:text-emerald-700 transition-colors truncate">{{ $brand->name }}</h3>
                                <p class="text-xs text-gray-400 mt-1 line-clamp-2 leading-relaxed h-[2.5rem]">{{ $brand->description ?? '' }}</p>

                                {{-- Meta row — pushed to bottom --}}
                                <div class="flex items-center justify-between mt-auto pt-3 border-t border-gray-100">
                                    <div class="flex items-center gap-3">
                                        @if ($brand->website_url)
                                            <span class="flex items-center gap-1 text-[11px] text-gray-400 truncate max-w-32">
                                                <x-heroicon-o-globe-alt class="w-3 h-3 shrink-0" />
                                                {{ parse_url($brand->website_url, PHP_URL_HOST) ?? $brand->website_url }}
                                            </span>
                                        @endif
                                        @if ($brand->outlets_count > 0)
                                            <span class="flex items-center gap-1 text-[11px] text-gray-400">
                                                <x-heroicon-o-building-storefront class="w-3 h-3" />
                                                {{ $brand->outlets_count }}
                                            </span>
                                        @endif
                                    </div>
                                    @if ($brand->user_id && $brand->adminUser)
                                        <span class="text-[10px] text-gray-400 truncate max-w-24" title="{{ $brand->adminUser->name }}">{{ $brand->adminUser->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- ROW VIEW --}}
            <div x-show="view === 'row'" class="space-y-2">
                @foreach ($brands as $brand)
                    <a href="{{ route('admin.brand-directory.edit', $brand) }}" class="group block">
                        <x-card class="p-4 hover:bg-gray-50/80 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-lg overflow-hidden shadow-sm ring-1 ring-black/5 flex items-center justify-center shrink-0"
                                     style="background: {{ $brand->logo_path ? '#ffffff' : ($brand->primary_color ?? '#6B7280') }}">
                                    @if ($brand->logo_path)
                                        <img src="{{ Storage::url($brand->logo_path) }}" alt="{{ $brand->name }}" class="w-full h-full object-contain p-1">
                                    @else
                                        <span class="text-white text-xs font-bold">{{ strtoupper(substr($brand->name, 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-semibold text-gray-900 group-hover:text-emerald-700 transition-colors truncate">{{ $brand->name }}</p>
                                        @if ($brand->primary_color)
                                            <span class="w-2.5 h-2.5 rounded-full shrink-0 ring-2 ring-white shadow-sm" style="background: {{ $brand->primary_color }}"></span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-400 truncate">{{ $brand->description ?? $brand->website_url ?? $brand->slug }}</p>
                                </div>
                                @if ($brand->outlets_count > 0)
                                    <span class="flex items-center gap-1 text-xs text-gray-400 shrink-0">
                                        <x-heroicon-o-building-storefront class="w-3.5 h-3.5" />
                                        {{ $brand->outlets_count }}
                                    </span>
                                @endif
                                <div class="shrink-0">
                                    @if ($brand->user_id)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-600 border border-blue-200">Claimed</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-600 border border-emerald-200">Available</span>
                                    @endif
                                </div>
                                <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-300 group-hover:text-emerald-500 transition-colors shrink-0" />
                            </div>
                        </x-card>
                    </a>
                @endforeach
            </div>

            {{-- Pagination + per-page --}}
            <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-4 mt-6">
                <div>
                    {{ $brands->withQueryString()->links() }}
                </div>
                <form method="GET" action="{{ route('admin.brand-directory.index') }}" class="flex items-center gap-2 text-sm text-gray-500">
                    <input type="hidden" name="search" value="{{ $currentSearch }}">
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                    <label for="per_page">Show</label>
                    <select name="per_page" id="per_page" onchange="this.form.submit()"
                            class="rounded-lg border-gray-200 text-sm py-1 pl-2 pr-7 focus:border-emerald-400 focus:ring-emerald-500/10 cursor-pointer">
                        @foreach ([18, 30, 60] as $size)
                            <option value="{{ $size }}" @selected($currentPerPage == $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span>per page</span>
                </form>
            </div>
        @endif
    </div>
</x-layouts.admin>

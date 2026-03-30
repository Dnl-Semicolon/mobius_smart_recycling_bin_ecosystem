<x-layouts.admin title="Brand Directory">
    <x-slot:header>
        Brand Directory
    </x-slot:header>

    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">{{ $brands->total() }} brands in catalog</p>
        <a href="{{ route('admin.brand-directory.create') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-emerald-600 text-white hover:bg-emerald-700 transition-colors shadow-sm">
            <x-heroicon-o-plus class="w-4 h-4" />
            Add Brand
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/60">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Brand</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Website</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Outlets</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($brands as $brand)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if ($brand->primary_color)
                                    <span class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center text-white text-[10px] font-bold shadow-sm"
                                          style="background: {{ $brand->primary_color }}">
                                        {{ strtoupper(substr($brand->name, 0, 2)) }}
                                    </span>
                                @else
                                    <span class="w-8 h-8 rounded-lg shrink-0 bg-gray-100 flex items-center justify-center">
                                        <x-heroicon-o-building-storefront class="w-4 h-4 text-gray-400" />
                                    </span>
                                @endif
                                <div>
                                    <span class="font-semibold text-gray-900 text-sm">{{ $brand->name }}</span>
                                    <p class="text-xs text-gray-400">{{ $brand->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-48">
                            {{ $brand->website_url ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if ($brand->user_id)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 border border-emerald-200">
                                    Claimed
                                </span>
                                @if ($brand->adminUser)
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $brand->adminUser->name }}</p>
                                @endif
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-0.5 text-xs font-medium text-gray-500 border border-gray-200">
                                    Available
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600 tabular-nums">{{ $brand->outlets_count }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.brand-directory.edit', $brand) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                                Edit
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $brands->links() }}
    </div>
</x-layouts.admin>

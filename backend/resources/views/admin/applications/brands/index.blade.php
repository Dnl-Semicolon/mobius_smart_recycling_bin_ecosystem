<x-layouts.admin title="Brand Applications">
    <x-slot:header>
        Brand Applications
    </x-slot:header>

    {{-- Status tabs --}}
    <div class="flex gap-2 mb-6">
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
            <a href="{{ route('admin.applications.brands.index', ['status' => $key]) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                   {{ $currentStatus === $key ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ $label }}
                @if ($key !== 'all' && ($counts[$key] ?? 0) > 0)
                    <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] rounded-full text-[10px] font-bold
                        {{ $currentStatus === $key ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-600' }}">
                        {{ $counts[$key] }}
                    </span>
                @endif
            </a>
        @endforeach

        <a href="{{ route('admin.applications.agencies.index') }}"
           class="ml-auto inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
            <x-heroicon-o-truck class="w-4 h-4" />
            Agency Applications
        </a>
    </div>

    @if ($applications->isEmpty())
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-building-storefront class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-400">No {{ $currentStatus }} brand applications</p>
        </x-card>
    @else
        <div class="space-y-3">
            @foreach ($applications as $app)
                <a href="{{ route('admin.applications.brands.show', $app) }}" class="block">
                    <x-card class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4">
                            @if ($app->brand && $app->brand->primary_color)
                                <span class="w-10 h-10 rounded-lg flex items-center justify-center text-white text-[10px] font-bold shadow-sm shrink-0"
                                      style="background: {{ $app->brand->primary_color }}">
                                    {{ strtoupper(substr($app->brand_name, 0, 2)) }}
                                </span>
                            @else
                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                    <x-heroicon-o-building-storefront class="w-5 h-5 text-gray-400" />
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $app->brand_name }}</p>
                                    @if ($app->brand_id)
                                        <span class="inline-flex items-center rounded-full bg-blue-50 px-1.5 py-0.5 text-[10px] font-medium text-blue-600 border border-blue-100">Claiming</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-violet-50 px-1.5 py-0.5 text-[10px] font-medium text-violet-600 border border-violet-100">New Brand</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 truncate">{{ $app->contact_email }} &middot; {{ $app->contact_person }}</p>
                            </div>
                            <div class="shrink-0">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'rejected' => 'bg-red-50 text-red-700 border-red-200',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium {{ $statusColors[$app->status->value] ?? '' }}">
                                    {{ $app->status->label() }}
                                </span>
                            </div>
                            <span class="text-xs text-gray-400 shrink-0">{{ $app->created_at->diffForHumans() }}</span>
                        </div>
                    </x-card>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $applications->withQueryString()->links() }}
        </div>
    @endif
</x-layouts.admin>

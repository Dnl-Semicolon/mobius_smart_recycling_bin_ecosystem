<x-layouts.admin title="Organizations">
    <x-slot:header>
        Organizations
    </x-slot:header>

    {{-- Tab switcher --}}
    <div class="flex gap-2 mb-6">
        <a href="{{ route('admin.organizations.index', ['tab' => 'organizations']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
               {{ $currentTab === 'organizations' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            Organizations
            <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] rounded-full text-[10px] font-bold
                {{ $currentTab === 'organizations' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-600' }}">
                {{ $organizations->total() }}
            </span>
        </a>
        <a href="{{ route('admin.organizations.index', ['tab' => 'requests']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
               {{ $currentTab === 'requests' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            Registration Requests
            @if ($pendingCount > 0)
                <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] rounded-full text-[10px] font-bold
                    {{ $currentTab === 'requests' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-700' }}">
                    {{ $pendingCount }}
                </span>
            @endif
        </a>
    </div>

    {{-- Organizations Tab --}}
    @if ($currentTab === 'organizations')
        @if ($organizations->isEmpty())
            <x-card variant="subtle" class="text-center py-16">
                <x-heroicon-o-building-office-2 class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                <p class="text-gray-400">No organizations yet</p>
                <p class="text-sm text-gray-400 mt-1">Organizations appear when registration requests are approved.</p>
            </x-card>
        @else
            <div class="space-y-3">
                @foreach ($organizations as $org)
                    <x-card :href="route('admin.organizations.show', $org)" :interactive="true" class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0 mt-0.5">
                                    <x-heroicon-o-building-office-2 class="w-5 h-5 text-gray-400" />
                                </div>
                                <div class="min-w-0">
                                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                                        {{ $org->name }}
                                        @if ($org->is_active)
                                            <span class="text-xs font-medium rounded-full px-2 py-0.5 bg-emerald-100 text-emerald-700">Active</span>
                                        @else
                                            <span class="text-xs font-medium rounded-full px-2 py-0.5 bg-gray-100 text-gray-600">Inactive</span>
                                        @endif
                                    </h2>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        {{ ucfirst(str_replace('_', ' ', $org->type ?? 'N/A')) }}
                                    </p>
                                    @if ($org->subscription?->plan)
                                        <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                                            <x-heroicon-o-credit-card class="w-3 h-3" />
                                            {{ $org->subscription->plan->name }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="shrink-0 flex flex-col items-end gap-1.5">
                                <span class="text-xs font-medium text-gray-500">
                                    {{ $org->brands_count }} {{ Str::plural('brand', $org->brands_count) }}
                                </span>
                                <span class="text-xs text-gray-400">
                                    {{ $org->users_count }} {{ Str::plural('user', $org->users_count) }}
                                </span>
                            </div>
                        </div>
                    </x-card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $organizations->withQueryString()->links() }}
            </div>
        @endif

    {{-- Registration Requests Tab --}}
    @else
        @if ($requests->isEmpty())
            <x-card variant="subtle" class="text-center py-16">
                <x-heroicon-o-clipboard-document-check class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                <p class="text-gray-400">No registration requests</p>
                <p class="text-sm text-gray-400 mt-1">Requests appear when companies register on the landing page.</p>
            </x-card>
        @else
            <div class="space-y-3">
                @foreach ($requests as $regRequest)
                    @php
                        $statusColor = match($regRequest->status) {
                            'approved' => 'bg-emerald-100 text-emerald-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            'pending' => 'bg-amber-100 text-amber-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <a href="{{ route('admin.registration-requests.show', $regRequest) }}" class="block bg-white rounded-xl border border-gray-200 shadow-sm hover:border-gray-300 hover:shadow transition-all p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0 mt-0.5">
                                    <x-heroicon-o-document-text class="w-5 h-5 text-gray-400" />
                                </div>
                                <div class="min-w-0">
                                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                                        {{ $regRequest->company_name }}
                                        <span class="text-xs font-medium rounded-full px-2 py-0.5 {{ $statusColor }}">
                                            {{ ucfirst($regRequest->status) }}
                                        </span>
                                    </h2>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        {{ $regRequest->contact_name }} &middot; {{ $regRequest->contact_email }} &middot; {{ $regRequest->contact_phone }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1.5 flex-wrap">
                                        {{ ucfirst(str_replace('_', ' ', $regRequest->type)) }}
                                        <span class="text-gray-300">&middot;</span>
                                        {{ $regRequest->created_at->diffForHumans() }}
                                        <span class="text-gray-300">&middot;</span>
                                        @if ($regRequest->phone_verified_at)
                                            <span class="inline-flex items-center gap-0.5 text-emerald-600">
                                                <x-heroicon-s-check-circle class="w-3 h-3" />
                                                Phone
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-0.5 text-amber-600">
                                                <x-heroicon-s-exclamation-triangle class="w-3 h-3" />
                                                Phone
                                            </span>
                                        @endif
                                        @if ($regRequest->email_verified_at)
                                            <span class="inline-flex items-center gap-0.5 text-emerald-600">
                                                <x-heroicon-s-check-circle class="w-3 h-3" />
                                                Email
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-0.5 text-amber-600">
                                                <x-heroicon-s-exclamation-triangle class="w-3 h-3" />
                                                Email
                                            </span>
                                        @endif
                                    </p>
                                    @if ($regRequest->description)
                                        <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $regRequest->description }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="shrink-0 text-right">
                                <span class="text-xs font-medium rounded-full px-2 py-0.5 {{ $statusColor }}">
                                    {{ ucfirst($regRequest->status) }}
                                </span>
                                @if ($regRequest->reviewer)
                                    <p class="text-[10px] text-gray-400 mt-1">by {{ $regRequest->reviewer->name }}</p>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $requests->withQueryString()->links() }}
            </div>
        @endif
    @endif
</x-layouts.admin>

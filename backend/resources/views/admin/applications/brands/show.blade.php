<x-layouts.admin title="Brand Application — {{ $application->brand_name }}">
    <x-slot:header>
        {{ $application->brand_name }}
    </x-slot:header>

    <x-slot:back>
        <a href="{{ route('admin.applications.brands.index') }}" class="topbar-icon-btn" title="Back">
            <x-heroicon-o-arrow-left class="w-5 h-5" />
        </a>
    </x-slot:back>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main info --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Application type badge --}}
            <div class="flex items-center gap-2">
                @if ($application->brand_id)
                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 border border-blue-200">
                        <x-heroicon-o-link class="w-3 h-3" />
                        Claiming existing brand
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2.5 py-1 text-xs font-medium text-violet-700 border border-violet-200">
                        <x-heroicon-o-plus class="w-3 h-3" />
                        New brand request
                    </span>
                @endif
            </div>

            {{-- Catalog brand info (if claiming existing) --}}
            @if ($application->brand)
                <x-card class="p-5 border-blue-200/60 bg-blue-50/30">
                    <h2 class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-3">Directory Brand</h2>
                    <div class="flex items-center gap-3">
                        @if ($application->brand->primary_color)
                            <span class="w-10 h-10 rounded-lg flex items-center justify-center text-white text-xs font-bold shadow-sm"
                                  style="background: {{ $application->brand->primary_color }}">
                                {{ strtoupper(substr($application->brand->name, 0, 2)) }}
                            </span>
                        @endif
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $application->brand->name }}</p>
                            <p class="text-xs text-gray-500">{{ $application->brand->website_url ?? $application->brand->description ?? 'No website' }}</p>
                        </div>
                    </div>
                </x-card>
            @endif

            <x-card class="p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">
                    @if ($application->brand_id)
                        Contact Information
                    @else
                        Brand & Contact Information
                    @endif
                </h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    @unless ($application->brand_id)
                        <div>
                            <dt class="text-gray-500">Brand Name</dt>
                            <dd class="font-medium text-gray-900 mt-0.5">{{ $application->brand_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Website</dt>
                            <dd class="font-medium text-gray-900 mt-0.5">{{ $application->website_url ?? '—' }}</dd>
                        </div>
                    @endunless
                    <div>
                        <dt class="text-gray-500">Contact Person</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $application->contact_person }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Email</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $application->contact_email }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Phone</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $application->contact_phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Submitted</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $application->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                </dl>
                @if ($application->description)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <dt class="text-sm text-gray-500 mb-1">Description</dt>
                        <dd class="text-sm text-gray-900">{{ $application->description }}</dd>
                    </div>
                @endif
            </x-card>

            @if ($application->status->value === 'rejected')
                <x-card class="p-6 border-red-200 bg-red-50/50">
                    <h2 class="text-sm font-semibold text-red-700 mb-2">Rejection Details</h2>
                    <p class="text-sm text-red-600">{{ $application->rejection_reason }}</p>
                    @if ($application->reviewer)
                        <p class="text-xs text-red-400 mt-2">Rejected by {{ $application->reviewer->name }} on {{ $application->reviewed_at->format('d M Y') }}</p>
                    @endif
                </x-card>
            @endif

            @if ($application->status->value === 'approved' && $application->reviewer)
                <x-card class="p-6 border-emerald-200 bg-emerald-50/50">
                    <h2 class="text-sm font-semibold text-emerald-700 mb-2">Approval Details</h2>
                    @if ($application->brand)
                        <p class="text-sm text-emerald-600">Multiplier: {{ $application->brand->points_multiplier }}x &middot; Budget: {{ number_format($application->brand->rewards_budget) }} pts</p>
                    @endif
                    <p class="text-xs text-emerald-400 mt-2">Approved by {{ $application->reviewer->name }} on {{ $application->reviewed_at->format('d M Y') }}</p>
                </x-card>
            @endif
        </div>

        {{-- Actions sidebar --}}
        <div class="space-y-6">
            @php
                $statusColors = [
                    'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'rejected' => 'bg-red-50 text-red-700 border-red-200',
                ];
            @endphp
            <x-card class="p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusColors[$application->status->value] ?? '' }}">
                        {{ $application->status->label() }}
                    </span>
                </div>

                @if ($application->logo_path)
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg flex items-center justify-center">
                        <img src="{{ Storage::url($application->logo_path) }}" alt="{{ $application->brand_name }}" class="max-h-24 object-contain">
                    </div>
                @endif

                @if ($application->user)
                    <div class="text-sm text-gray-500 mb-4">
                        <span class="font-medium text-gray-700">Account:</span> {{ $application->user->email }}
                    </div>
                @endif
            </x-card>

            @if ($application->status->value === 'pending')
                {{-- Approve form --}}
                <x-card class="p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Approve Application</h3>
                    <form method="POST" action="{{ route('admin.applications.brands.approve', $application) }}" class="space-y-3">
                        @csrf
                        <x-input
                            name="points_multiplier"
                            type="number"
                            label="Points Multiplier"
                            placeholder="e.g. 1.50"
                            step="0.01"
                            min="1.00"
                            max="5.00"
                            value="1.50"
                            required
                        />
                        <x-input
                            name="rewards_budget"
                            type="number"
                            label="Rewards Budget"
                            placeholder="e.g. 10000"
                            min="0"
                            value="10000"
                            required
                        />
                        <x-button type="submit" class="w-full justify-center bg-emerald-600 hover:bg-emerald-700">
                            <x-heroicon-o-check class="w-4 h-4" />
                            Approve
                        </x-button>
                    </form>
                </x-card>

                {{-- Reject form --}}
                <x-card class="p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Reject Application</h3>
                    <form method="POST" action="{{ route('admin.applications.brands.reject', $application) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                            <textarea
                                name="rejection_reason"
                                rows="3"
                                required
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-400 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"
                                placeholder="Reason for rejection..."
                            ></textarea>
                            @error('rejection_reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <x-button type="submit" variant="secondary" class="w-full justify-center text-red-600 hover:bg-red-50">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                            Reject
                        </x-button>
                    </form>
                </x-card>
            @endif
        </div>
    </div>
</x-layouts.admin>

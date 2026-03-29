<x-layouts.admin title="Brand Application — {{ $brand->name }}">
    <x-slot:header>
        {{ $brand->name }}
    </x-slot:header>

    <x-slot:back>
        <a href="{{ route('admin.applications.brands.index') }}" class="topbar-icon-btn" title="Back">
            <x-heroicon-o-arrow-left class="w-5 h-5" />
        </a>
    </x-slot:back>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main info --}}
        <div class="lg:col-span-2 space-y-6">
            <x-card class="p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Company Information</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Brand Name</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $brand->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Contact Person</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $brand->contact_person ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Email</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $brand->contact_email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Phone</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $brand->contact_phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Website</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $brand->website_url ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Submitted</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $brand->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                </dl>
                @if ($brand->description)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <dt class="text-sm text-gray-500 mb-1">Description</dt>
                        <dd class="text-sm text-gray-900">{{ $brand->description }}</dd>
                    </div>
                @endif
            </x-card>

            @if ($brand->status->value === 'rejected')
                <x-card class="p-6 border-red-200 bg-red-50/50">
                    <h2 class="text-sm font-semibold text-red-700 mb-2">Rejection Details</h2>
                    <p class="text-sm text-red-600">{{ $brand->rejection_reason }}</p>
                    @if ($brand->reviewer)
                        <p class="text-xs text-red-400 mt-2">Rejected by {{ $brand->reviewer->name }} on {{ $brand->reviewed_at->format('d M Y') }}</p>
                    @endif
                </x-card>
            @endif

            @if ($brand->status->value === 'approved' && $brand->reviewer)
                <x-card class="p-6 border-emerald-200 bg-emerald-50/50">
                    <h2 class="text-sm font-semibold text-emerald-700 mb-2">Approval Details</h2>
                    <p class="text-sm text-emerald-600">Multiplier: {{ $brand->points_multiplier }}x &middot; Budget: {{ number_format($brand->rewards_budget) }} pts</p>
                    <p class="text-xs text-emerald-400 mt-2">Approved by {{ $brand->reviewer->name }} on {{ $brand->reviewed_at->format('d M Y') }}</p>
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
                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusColors[$brand->status->value] ?? '' }}">
                        {{ $brand->status->label() }}
                    </span>
                </div>

                @if ($brand->logo_path)
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg flex items-center justify-center">
                        <img src="{{ Storage::url($brand->logo_path) }}" alt="{{ $brand->name }}" class="max-h-24 object-contain">
                    </div>
                @endif

                @if ($brand->adminUser)
                    <div class="text-sm text-gray-500 mb-4">
                        <span class="font-medium text-gray-700">Account:</span> {{ $brand->adminUser->email }}
                    </div>
                @endif
            </x-card>

            @if ($brand->status->value === 'pending')
                {{-- Approve form --}}
                <x-card class="p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Approve Application</h3>
                    <form method="POST" action="{{ route('admin.applications.brands.approve', $brand) }}" class="space-y-3">
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
                    <form method="POST" action="{{ route('admin.applications.brands.reject', $brand) }}" class="space-y-3">
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

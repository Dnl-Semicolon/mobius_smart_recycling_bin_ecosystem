<x-layouts.admin title="Agency Application — {{ $agency->name }}">
    <x-slot:header>
        {{ $agency->name }}
    </x-slot:header>

    <x-slot:back>
        <a href="{{ route('admin.applications.agencies.index') }}" class="topbar-icon-btn" title="Back">
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
                        <dt class="text-gray-500">Company Name</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $agency->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Contact Person</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $agency->contact_person }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Email</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $agency->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Phone</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $agency->phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Fleet Size</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $agency->fleet_size }} vehicles</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Coverage Area</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $agency->coverage_area ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Submitted</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $agency->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                </dl>
                @if ($agency->description)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <dt class="text-sm text-gray-500 mb-1">Description</dt>
                        <dd class="text-sm text-gray-900">{{ $agency->description }}</dd>
                    </div>
                @endif
            </x-card>

            @if ($agency->status->value === 'rejected')
                <x-card class="p-6 border-red-200 bg-red-50/50">
                    <h2 class="text-sm font-semibold text-red-700 mb-2">Rejection Details</h2>
                    <p class="text-sm text-red-600">{{ $agency->rejection_reason }}</p>
                    @if ($agency->reviewer)
                        <p class="text-xs text-red-400 mt-2">Rejected by {{ $agency->reviewer->name }} on {{ $agency->reviewed_at->format('d M Y') }}</p>
                    @endif
                </x-card>
            @endif

            @if ($agency->status->value === 'approved' && $agency->reviewer)
                <x-card class="p-6 border-emerald-200 bg-emerald-50/50">
                    <h2 class="text-sm font-semibold text-emerald-700 mb-2">Approval Details</h2>
                    <p class="text-xs text-emerald-400">Approved by {{ $agency->reviewer->name }} on {{ $agency->reviewed_at->format('d M Y') }}</p>
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
                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusColors[$agency->status->value] ?? '' }}">
                        {{ $agency->status->label() }}
                    </span>
                </div>

                @if ($agency->logo_path)
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg flex items-center justify-center">
                        <img src="{{ Storage::url($agency->logo_path) }}" alt="{{ $agency->name }}" class="max-h-24 object-contain">
                    </div>
                @endif

                @if ($agency->adminUser)
                    <div class="text-sm text-gray-500 mb-4">
                        <span class="font-medium text-gray-700">Account:</span> {{ $agency->adminUser->email }}
                    </div>
                @endif
            </x-card>

            @if ($agency->status->value === 'pending')
                {{-- Approve --}}
                <x-card class="p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Approve Application</h3>
                    <form method="POST" action="{{ route('admin.applications.agencies.approve', $agency) }}">
                        @csrf
                        <x-button type="submit" class="w-full justify-center bg-emerald-600 hover:bg-emerald-700">
                            <x-heroicon-o-check class="w-4 h-4" />
                            Approve Agency
                        </x-button>
                    </form>
                </x-card>

                {{-- Reject --}}
                <x-card class="p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Reject Application</h3>
                    <form method="POST" action="{{ route('admin.applications.agencies.reject', $agency) }}" class="space-y-3">
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

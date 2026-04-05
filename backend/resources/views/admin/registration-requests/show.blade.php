<x-layouts.admin :title="$registrationRequest->company_name . ' — Registration Request'">
    <x-slot:back>
        <x-back-button href="{{ route('admin.organizations.index', ['tab' => 'requests']) }}" />
    </x-slot:back>

    <x-slot:header>
        Registration Request
    </x-slot:header>

    <div class="space-y-5">
        {{-- Details card --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 lg:p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center ring-1 ring-gray-200">
                    <x-heroicon-s-document-text class="w-6 h-6 text-gray-400" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $registrationRequest->company_name }}</h2>
                    <p class="text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $registrationRequest->type)) }}</p>
                </div>
                @php
                    $statusColor = match($registrationRequest->status) {
                        'approved' => 'bg-emerald-100 text-emerald-700 ring-emerald-200/60',
                        'rejected' => 'bg-red-100 text-red-700 ring-red-200/60',
                        'pending' => 'bg-amber-100 text-amber-700 ring-amber-200/60',
                        default => 'bg-gray-100 text-gray-600 ring-gray-200/60',
                    };
                @endphp
                <span class="ml-auto text-xs font-semibold rounded-full px-2.5 py-1 ring-1 {{ $statusColor }}">
                    {{ ucfirst($registrationRequest->status) }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- Contact Name --}}
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Contact Name</p>
                    <p class="font-semibold text-gray-900 mt-0.5">{{ $registrationRequest->contact_name }}</p>
                </div>

                {{-- Contact Email --}}
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Contact Email</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <p class="font-semibold text-gray-900">{{ $registrationRequest->contact_email }}</p>
                        @if ($registrationRequest->email_verified_at)
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold rounded-full px-2 py-0.5 bg-emerald-100 text-emerald-700">
                                <x-heroicon-s-check-circle class="w-3 h-3" />
                                Verified
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold rounded-full px-2 py-0.5 bg-amber-100 text-amber-700">
                                <x-heroicon-s-exclamation-triangle class="w-3 h-3" />
                                Unverified
                            </span>
                        @endif
                    </div>
                    @if ($registrationRequest->email_verified_at)
                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $registrationRequest->email_verified_at->format('d M Y, H:i') }}</p>
                    @endif
                </div>

                {{-- Contact Phone --}}
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Contact Phone</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <p class="font-semibold text-gray-900">{{ $registrationRequest->contact_phone }}</p>
                        @if ($registrationRequest->phone_verified_at)
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold rounded-full px-2 py-0.5 bg-emerald-100 text-emerald-700">
                                <x-heroicon-s-check-circle class="w-3 h-3" />
                                Verified
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold rounded-full px-2 py-0.5 bg-amber-100 text-amber-700">
                                <x-heroicon-s-exclamation-triangle class="w-3 h-3" />
                                Unverified
                            </span>
                        @endif
                    </div>
                    @if ($registrationRequest->phone_verified_at)
                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $registrationRequest->phone_verified_at->format('d M Y, H:i') }}</p>
                    @endif
                </div>

                {{-- Type --}}
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Type</p>
                    <p class="font-semibold text-gray-900 mt-0.5">{{ ucfirst(str_replace('_', ' ', $registrationRequest->type)) }}</p>
                </div>

                {{-- Description --}}
                @if ($registrationRequest->description)
                    <div class="sm:col-span-2">
                        <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Description</p>
                        <p class="text-gray-700 mt-0.5 leading-relaxed">{{ $registrationRequest->description }}</p>
                    </div>
                @endif

                {{-- Submitted --}}
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Submitted</p>
                    <p class="font-semibold text-gray-900 mt-0.5">{{ $registrationRequest->created_at->format('d M Y, H:i') }}</p>
                    <p class="text-[11px] text-gray-400">{{ $registrationRequest->created_at->diffForHumans() }}</p>
                </div>

                {{-- Reviewed by --}}
                @if ($registrationRequest->reviewer)
                    <div>
                        <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Reviewed By</p>
                        <p class="font-semibold text-gray-900 mt-0.5">{{ $registrationRequest->reviewer->name }}</p>
                        @if ($registrationRequest->reviewed_at)
                            <p class="text-[11px] text-gray-400">{{ $registrationRequest->reviewed_at->format('d M Y, H:i') }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Admin notes --}}
        @if ($registrationRequest->admin_notes)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 lg:p-6">
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center ring-1 ring-blue-200/60">
                        <x-heroicon-s-chat-bubble-left-ellipsis class="w-4 h-4 text-blue-600" />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Admin Notes</h3>
                </div>
                <p class="text-sm text-gray-700 leading-relaxed">{{ $registrationRequest->admin_notes }}</p>
            </div>
        @endif

        {{-- Actions (only for pending) --}}
        @if ($registrationRequest->isPending())
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 lg:p-6">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center ring-1 ring-gray-200/60">
                        <x-heroicon-s-cog-6-tooth class="w-4 h-4 text-gray-600" />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Actions</h3>
                </div>

                <div class="flex flex-col sm:flex-row items-start gap-4">
                    {{-- Approve --}}
                    <form action="{{ route('admin.registration-requests.approve', $registrationRequest) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors cursor-pointer shadow-sm">
                            <x-heroicon-s-check class="w-4 h-4" />
                            Approve Request
                        </button>
                    </form>

                    {{-- Reject --}}
                    <div x-data="{ showReject: false }" class="relative">
                        <button
                            type="button"
                            @click="showReject = !showReject"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-red-50 text-red-600 hover:bg-red-100 transition-colors cursor-pointer"
                        >
                            <x-heroicon-s-x-mark class="w-4 h-4" />
                            Reject Request
                        </button>

                        <div x-show="showReject" x-cloak x-transition class="mt-3 w-full sm:w-80 bg-white rounded-xl border border-gray-200 shadow-lg p-4">
                            <form action="{{ route('admin.registration-requests.reject', $registrationRequest) }}" method="POST">
                                @csrf
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Reason for rejection</label>
                                <textarea name="admin_notes" rows="3" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-red-400 focus:ring-2 focus:ring-red-500/10" placeholder="Explain why this request is being rejected..."></textarea>
                                <div class="flex justify-end gap-2 mt-3">
                                    <button type="button" @click="showReject = false" class="px-3 py-1.5 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-100 cursor-pointer">Cancel</button>
                                    <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-red-600 text-white hover:bg-red-700 cursor-pointer">Confirm Reject</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.admin>

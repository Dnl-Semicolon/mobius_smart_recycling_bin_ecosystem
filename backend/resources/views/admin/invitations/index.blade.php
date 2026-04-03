<x-layouts.admin title="Invitations">
    <x-slot:header>
        Invitations
    </x-slot:header>

    {{-- Status filter tabs --}}
    <div class="flex gap-2 mb-6">
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'accepted' => 'Accepted', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
            <a href="{{ route('admin.invitations.index', ['status' => $key]) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                   {{ $currentStatus === $key ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ $label }}
                @if ($key !== 'all' && ($counts[$key] ?? 0) > 0)
                    <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] rounded-full text-[10px] font-bold
                        {{ $currentStatus === $key ? 'bg-white/20 text-white' : ($key === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-gray-200 text-gray-600') }}">
                        {{ $counts[$key] }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>

    @if ($invitations->isEmpty())
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-envelope class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-400">No invitations found</p>
            <p class="text-sm text-gray-400 mt-1">Invitations appear when organizations invite new members.</p>
        </x-card>
    @else
        <div class="space-y-3">
            @foreach ($invitations as $invitation)
                @php
                    $statusColor = match($invitation->status) {
                        'approved' => 'bg-emerald-100 text-emerald-700',
                        'accepted' => 'bg-blue-100 text-blue-700',
                        'rejected' => 'bg-red-100 text-red-700',
                        'pending' => 'bg-amber-100 text-amber-700',
                        default => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <x-card class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0 mt-0.5">
                                <x-heroicon-o-envelope class="w-5 h-5 text-gray-400" />
                            </div>
                            <div class="min-w-0">
                                <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                                    {{ $invitation->name }}
                                    <span class="text-xs font-medium rounded-full px-2 py-0.5 {{ $statusColor }}">
                                        {{ ucfirst($invitation->status) }}
                                    </span>
                                </h2>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    {{ $invitation->email }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1">
                                    <x-heroicon-o-building-office-2 class="w-3 h-3" />
                                    {{ $invitation->organization->name ?? 'Unknown Org' }}
                                    <span class="text-gray-300 mx-0.5">&middot;</span>
                                    Role: {{ ucfirst(str_replace('_', ' ', $invitation->role)) }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Invited by {{ $invitation->invitedBy->name ?? 'Unknown' }}
                                    <span class="text-gray-300 mx-0.5">&middot;</span>
                                    {{ $invitation->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>

                        {{-- Approve / Reject actions (only for pending) --}}
                        @if ($invitation->isPending())
                            <div class="shrink-0 flex items-center gap-2" x-data="{ showReject: false }">
                                <form action="{{ route('admin.invitations.admin-approve', $invitation) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-emerald-600 text-white hover:bg-emerald-700 transition-colors cursor-pointer">
                                        <x-heroicon-o-check class="w-3.5 h-3.5" />
                                        Approve
                                    </button>
                                </form>
                                <button
                                    type="button"
                                    @click="showReject = !showReject"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition-colors cursor-pointer"
                                >
                                    <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                                    Reject
                                </button>

                                {{-- Reject form (expandable) --}}
                                <div x-show="showReject" x-cloak x-transition class="absolute right-0 top-full mt-2 z-10 w-72 bg-white rounded-xl border border-gray-200 shadow-lg p-4">
                                    <form action="{{ route('admin.invitations.admin-reject', $invitation) }}" method="POST">
                                        @csrf
                                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Reason for rejection</label>
                                        <textarea name="admin_notes" rows="3" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-red-400 focus:ring-2 focus:ring-red-500/10" placeholder="Explain why this invitation is being rejected..."></textarea>
                                        <div class="flex justify-end gap-2 mt-2">
                                            <button type="button" @click="showReject = false" class="px-3 py-1.5 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-100 cursor-pointer">Cancel</button>
                                            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-red-600 text-white hover:bg-red-700 cursor-pointer">Confirm Reject</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="shrink-0 text-right">
                                <span class="text-xs font-medium rounded-full px-2 py-0.5 {{ $statusColor }}">
                                    {{ ucfirst($invitation->status) }}
                                </span>
                                @if ($invitation->approvedBy)
                                    <p class="text-[10px] text-gray-400 mt-1">by {{ $invitation->approvedBy->name }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </x-card>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $invitations->withQueryString()->links() }}
        </div>
    @endif
</x-layouts.admin>

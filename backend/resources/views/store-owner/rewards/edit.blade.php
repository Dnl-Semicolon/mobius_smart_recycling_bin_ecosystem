<x-layouts.store-owner :title="'Edit ' . $reward->name">
    <x-slot:back>
        <x-back-button href="{{ route('store.rewards.index') }}" />
    </x-slot:back>

    <x-slot:header>
        Edit Reward
    </x-slot:header>

    <form action="{{ route('store.rewards.update', $reward) }}" method="POST">
        @csrf
        @method('PUT')

        @include('store-owner.rewards._form')

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 mt-8">
            <a
                href="{{ route('store.rewards.index') }}"
                class="rounded-full px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
            >
                Cancel
            </a>
            <button
                type="submit"
                class="inline-flex items-center gap-2 rounded-full px-5 py-2 text-sm font-medium bg-emerald-600 text-white hover:bg-emerald-700 focus-visible:ring-2 focus-visible:ring-emerald-500/30 focus-visible:ring-offset-2"
            >
                <x-heroicon-o-check class="w-4 h-4" />
                Update Reward
            </button>
        </div>
    </form>

    {{-- Danger zone --}}
    <div class="mt-10 pt-6 border-t border-red-200/60">
        <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-x-10 gap-y-4">
            <div class="lg:pt-1">
                <div class="flex items-center gap-2.5 mb-1.5">
                    <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center ring-1 ring-red-200/60">
                        <x-heroicon-s-exclamation-triangle class="w-4 h-4 text-red-600" />
                    </div>
                    <h2 class="text-sm font-semibold text-red-700">Danger Zone</h2>
                </div>
                <p class="text-xs text-gray-500 leading-relaxed lg:pl-[42px]">Permanently remove this reward.</p>
            </div>

            <div class="rounded-xl bg-white border border-red-200/60 shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Delete this reward</p>
                        <p class="text-xs text-gray-500 mt-0.5">Once deleted, this cannot be undone. Existing redemptions will be preserved.</p>
                    </div>
                    <form method="POST" action="{{ route('store.rewards.destroy', $reward) }}" onsubmit="return confirm('Delete this reward? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-red-300 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 hover:border-red-400 transition-colors cursor-pointer">
                            <x-heroicon-o-trash class="w-3.5 h-3.5" />
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.store-owner>

<x-layouts.admin title="Create Plan">
    <x-slot:back>
        <x-back-button href="{{ route('admin.plans.index') }}" />
    </x-slot:back>

    <x-slot:header>
        New Plan
    </x-slot:header>

    <form action="{{ route('admin.plans.store') }}" method="POST">
        @csrf

        @include('admin.plans._form')

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 mt-8">
            <a
                href="{{ route('admin.plans.index') }}"
                class="rounded-full px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
            >
                Cancel
            </a>
            <button
                type="submit"
                class="inline-flex items-center gap-2 rounded-full px-5 py-2 text-sm font-medium bg-emerald-600 text-white hover:bg-emerald-700 focus-visible:ring-2 focus-visible:ring-emerald-500/30 focus-visible:ring-offset-2"
            >
                <x-heroicon-o-check class="w-4 h-4" />
                Create Plan
            </button>
        </div>
    </form>
</x-layouts.admin>

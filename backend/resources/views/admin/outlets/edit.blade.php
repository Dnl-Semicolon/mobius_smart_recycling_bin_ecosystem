<x-layouts.admin :title="'Edit ' . $outlet->name">
    <x-slot:back>
        <x-back-button href="{{ route('admin.outlets.show', $outlet) }}" />
    </x-slot:back>

    <x-slot:header>
        Edit Outlet
    </x-slot:header>

    <form action="{{ route('admin.outlets.update', $outlet) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('admin.outlets._form')

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 mt-8">
            <a
                href="{{ route('admin.outlets.show', $outlet) }}"
                class="rounded-full px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
            >
                Cancel
            </a>
            <button
                type="submit"
                class="inline-flex items-center gap-2 rounded-full px-5 py-2 text-sm font-medium bg-emerald-600 text-white hover:bg-emerald-700 focus-visible:ring-2 focus-visible:ring-emerald-500/30 focus-visible:ring-offset-2"
            >
                <x-heroicon-o-check class="w-4 h-4" />
                Update Outlet
            </button>
        </div>
    </form>
</x-layouts.admin>

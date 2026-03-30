<x-layouts.admin title="{{ $brand ? 'Edit ' . $brand->name : 'Add Brand' }}">
    <x-slot:header>
        {{ $brand ? 'Edit ' . $brand->name : 'Add Brand to Directory' }}
    </x-slot:header>

    <x-slot:back>
        <a href="{{ route('admin.brand-directory.index') }}" class="topbar-icon-btn" title="Back">
            <x-heroicon-o-arrow-left class="w-5 h-5" />
        </a>
    </x-slot:back>

    <div class="max-w-xl">
        <x-card class="p-6">
            <form
                method="POST"
                action="{{ $brand ? route('admin.brand-directory.update', $brand) : route('admin.brand-directory.store') }}"
                enctype="multipart/form-data"
                class="space-y-5"
            >
                @csrf
                @if ($brand)
                    @method('PUT')
                @endif

                <x-input
                    name="name"
                    label="Brand Name"
                    placeholder="e.g. Starbucks"
                    :value="$brand?->name"
                    required
                />

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1.5">Description</label>
                    <textarea
                        name="description"
                        rows="3"
                        class="w-full rounded-xl border border-gray-200/80 bg-white/60 px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 focus:outline-none"
                        placeholder="Brief description of the brand"
                    >{{ old('description', $brand?->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <x-input
                    name="website_url"
                    type="url"
                    label="Website"
                    placeholder="https://example.com"
                    :value="$brand?->website_url"
                />

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1.5">Primary Color</label>
                    <div class="flex items-center gap-3">
                        <input
                            type="color"
                            name="primary_color"
                            value="{{ old('primary_color', $brand?->primary_color ?? '#6B7280') }}"
                            class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5"
                        >
                        <input
                            type="text"
                            value="{{ old('primary_color', $brand?->primary_color ?? '') }}"
                            class="w-24 rounded-xl border border-gray-200/80 bg-white/60 px-3 py-2 text-sm text-gray-900 font-mono"
                            placeholder="#000000"
                            readonly
                            x-data
                            x-ref="hex"
                        >
                    </div>
                    @error('primary_color')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1.5">Logo</label>
                    @if ($brand?->logo_path)
                        <div class="mb-2 p-3 bg-gray-50 rounded-lg inline-flex items-center gap-2">
                            <img src="{{ Storage::url($brand->logo_path) }}" alt="" class="h-8 object-contain">
                            <span class="text-xs text-gray-400">Current logo</span>
                        </div>
                    @endif
                    <input
                        type="file"
                        name="logo"
                        accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 file:cursor-pointer"
                    >
                    @error('logo')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-button type="submit" class="justify-center">
                        <x-heroicon-o-check class="w-4 h-4" />
                        {{ $brand ? 'Save Changes' : 'Add Brand' }}
                    </x-button>
                    <a href="{{ route('admin.brand-directory.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.admin>

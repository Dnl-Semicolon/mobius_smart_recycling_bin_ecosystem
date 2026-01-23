<x-layouts.admin title="Create Bin">
    <x-slot:back>
        <x-back-button href="{{ route('admin.bins.index') }}" />
    </x-slot:back>

    <x-slot:header>
        New Bin
    </x-slot:header>

    <x-card>
        <form action="{{ route('admin.bins.store') }}" method="POST" class="space-y-8">
            @csrf

            {{-- Bin Info Section --}}
            <div class="form-section">
                <h2 class="form-section-title">Bin Info</h2>
                <div class="space-y-4">
                    <x-input
                        name="serial_number"
                        label="Serial Number"
                        placeholder="e.g. MBR-2024-001"
                        hint="Unique identifier for this bin"
                        required
                    />

                    <div>
                        <label for="fill_level" class="block text-sm font-medium text-gray-700 mb-1">
                            Fill Level
                        </label>
                        <div class="flex items-center gap-4">
                            <input
                                type="range"
                                name="fill_level"
                                id="fill_level"
                                min="0"
                                max="100"
                                value="{{ old('fill_level', 0) }}"
                                class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                oninput="document.getElementById('fill_level_display').textContent = this.value + '%'"
                            >
                            <span id="fill_level_display" class="text-sm font-medium text-gray-700 w-12 text-right">
                                {{ old('fill_level', 0) }}%
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Current fill level (0-100%)</p>
                        @error('fill_level')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-select
                        name="status"
                        label="Status"
                        :options="collect($statuses)->mapWithKeys(fn($s) => [$s->value => ucfirst($s->value)])->toArray()"
                        placeholder="Select status"
                    />
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit">
                    Create Bin
                </x-button>
                <x-button href="{{ route('admin.bins.index') }}" variant="ghost">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-card>
</x-layouts.admin>

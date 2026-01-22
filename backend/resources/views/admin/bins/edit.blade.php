<x-layouts.app :title="'Edit ' . $bin->serial_number">
    <x-slot:back>
        <x-back-button href="{{ route('admin.bins.show', $bin) }}" />
    </x-slot:back>

    <x-slot:header>
        Edit Bin
    </x-slot:header>

    <x-card>
        <form action="{{ route('admin.bins.update', $bin) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            {{-- Bin Info Section --}}
            <div class="form-section">
                <h2 class="form-section-title">Bin Info</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Serial Number
                        </label>
                        <p class="text-gray-900 font-mono bg-gray-50 rounded-xl px-4 py-3">
                            {{ $bin->serial_number }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">Serial number cannot be changed</p>
                    </div>

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
                                value="{{ old('fill_level', $bin->fill_level) }}"
                                class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                oninput="document.getElementById('fill_level_display').textContent = this.value + '%'"
                            >
                            <span id="fill_level_display" class="text-sm font-medium text-gray-700 w-12 text-right">
                                {{ old('fill_level', $bin->fill_level) }}%
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
                        :value="old('status', $bin->status->value)"
                    />
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit">
                    Update Bin
                </x-button>
                <x-button href="{{ route('admin.bins.show', $bin) }}" variant="ghost">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

<x-layouts.app title="Create Outlet">
    <x-slot:back>
        <x-back-button href="{{ route('admin.outlets.index') }}" />
    </x-slot:back>

    <x-slot:header>
        New Outlet
    </x-slot:header>

    <x-card>
        <form action="{{ route('admin.outlets.store') }}" method="POST" class="space-y-8">
            @csrf

            {{-- Basic Info Section --}}
            <div class="form-section">
                <h2 class="form-section-title">Basic Info</h2>
                <div class="space-y-4">
                    <x-input
                        name="name"
                        label="Outlet Name"
                        placeholder="e.g. Starbucks KLCC"
                        required
                    />

                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                            Address <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            name="address"
                            id="address"
                            rows="3"
                            placeholder="Full street address"
                            required
                            class="w-full rounded-xl border-gray-200 text-sm focus:border-gray-300 focus:ring focus:ring-gray-200 focus:ring-opacity-50 @error('address') border-red-300 @enderror"
                        >{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <x-input
                            name="latitude"
                            label="Latitude"
                            type="number"
                            step="any"
                            placeholder="-90 to 90"
                            hint="e.g. 3.1570"
                        />

                        <x-input
                            name="longitude"
                            label="Longitude"
                            type="number"
                            step="any"
                            placeholder="-180 to 180"
                            hint="e.g. 101.7118"
                        />
                    </div>

                    <x-input
                        name="operating_hours"
                        label="Operating Hours"
                        placeholder="e.g. Mon-Fri 8am-10pm, Sat-Sun 9am-11pm"
                    />

                    <x-select
                        name="contract_status"
                        label="Contract Status"
                        :options="collect($statuses)->mapWithKeys(fn($s) => [$s->value => ucfirst($s->value)])->toArray()"
                        placeholder="Select status"
                    />
                </div>
            </div>

            {{-- Contact Info Section --}}
            <div class="form-section">
                <h2 class="form-section-title">Contact Info</h2>
                <div class="space-y-4">
                    <x-input
                        name="contact_name"
                        label="Contact Name"
                        placeholder="Person in charge"
                    />

                    <x-input
                        name="contact_phone"
                        label="Contact Phone"
                        placeholder="01X-XXX XXXX"
                        hint="Malaysian format"
                    />

                    <x-input
                        name="contact_email"
                        label="Contact Email"
                        type="email"
                        placeholder="email@example.com"
                    />
                </div>
            </div>

            {{-- Notes Section --}}
            <div class="form-section">
                <h2 class="form-section-title">Notes</h2>
                <div>
                    <textarea
                        name="notes"
                        id="notes"
                        rows="4"
                        placeholder="Additional notes about this outlet..."
                        class="w-full rounded-xl border-gray-200 text-sm focus:border-gray-300 focus:ring focus:ring-gray-200 focus:ring-opacity-50 @error('notes') border-red-300 @enderror"
                    >{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit">
                    Create Outlet
                </x-button>
                <x-button href="{{ route('admin.outlets.index') }}" variant="ghost">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

<x-layouts.app title="Create Person">
    <x-slot:back>
        <x-back-button href="{{ route('persons.index') }}" />
    </x-slot:back>

    <x-slot:header>
        New Person
    </x-slot:header>

    <x-card>
        <form action="{{ route('persons.store') }}" method="POST" class="space-y-8">
            @csrf

            {{-- Personal Info Section --}}
            <div class="form-section">
                <h2 class="form-section-title">Personal Info</h2>
                <div class="space-y-4">
                    <x-input
                        name="name"
                        label="Name"
                        placeholder="Full name"
                        required
                    />

                    <x-input
                        name="birthday"
                        label="Birthday"
                        type="date"
                        required
                    />

                    <x-input
                        name="phone"
                        label="Phone"
                        placeholder="01X-XXX XXXX"
                        hint="Malaysian format: 01X-XXX XXXX or 01X-XXXX XXXX"
                        required
                    />
                </div>
            </div>

            {{-- Address Section --}}
            <div class="form-section">
                <h2 class="form-section-title">Address</h2>
                <div class="space-y-4">
                    <x-input
                        name="line_1"
                        label="Address Line 1"
                        placeholder="Street address"
                        required
                    />

                    <x-input
                        name="line_2"
                        label="Address Line 2"
                        placeholder="Apartment, unit, etc. (optional)"
                    />

                    <div class="grid grid-cols-2 gap-4">
                        <x-input
                            name="city"
                            label="City"
                            placeholder="City"
                            required
                        />

                        <x-select
                            name="state"
                            label="State"
                            :options="$states"
                            placeholder="Select state"
                            required
                        />
                    </div>

                    <x-input
                        name="postal_code"
                        label="Postal Code"
                        placeholder="5-digit postal code"
                        hint="e.g. 50000"
                        required
                    />
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit">
                    Create Person
                </x-button>
                <x-button href="{{ route('persons.index') }}" variant="ghost">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>

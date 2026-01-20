@php
    $address = $person->addresses->first();

    $original = [
        'name' => $person->name,
        'birthday' => $person->birthday?->format('Y-m-d'),
        'phone' => $person->phone,
        'line_1' => $address?->line_1,
        'line_2' => $address?->line_2 ?? '',
        'city' => $address?->city,
        'state' => $address?->state,
        'postal_code' => $address?->postal_code,
    ];

    $labels = [
        'name' => 'Name',
        'birthday' => 'Birthday',
        'phone' => 'Phone',
        'line_1' => 'Address Line 1',
        'line_2' => 'Address Line 2',
        'city' => 'City',
        'state' => 'State',
        'postal_code' => 'Postal Code',
    ];
@endphp

<x-layouts.app :title="'Edit ' . $person->name">
    <x-slot:back>
        <x-back-button href="{{ route('persons.show', $person) }}" />
    </x-slot:back>

    <x-slot:header>
        Edit Person
    </x-slot:header>

    <x-card>
        <form
            action="{{ route('persons.update', $person) }}"
            method="POST"
            class="space-y-8"
            hx-boost="false"
            onsubmit="return window.confirmChanges ? window.confirmChanges(this, {{ Js::from($original) }}, {{ Js::from($labels) }}, '{{ route('persons.show', $person) }}') : true"
        >
            @csrf
            @method('PUT')

            {{-- Personal Info Section --}}
            <div class="form-section">
                <h2 class="form-section-title">Personal Info</h2>
                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <label for="name" class="block text-sm font-medium text-gray-600">Name</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $original['name']) }}"
                            placeholder="Full name"
                            required
                            class="w-full rounded-xl border border-gray-200/80 bg-white/60 px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-gray-300 focus:bg-white focus:ring-2 focus:ring-black/5"
                        >
                        <p class="text-xs text-gray-400">Was: {{ $original['name'] }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label for="birthday" class="block text-sm font-medium text-gray-600">Birthday</label>
                        <input
                            id="birthday"
                            name="birthday"
                            type="date"
                            value="{{ old('birthday', $original['birthday']) }}"
                            required
                            class="w-full rounded-xl border border-gray-200/80 bg-white/60 px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-gray-300 focus:bg-white focus:ring-2 focus:ring-black/5"
                        >
                        <p class="text-xs text-gray-400">Was: {{ $person->birthday?->format('d M Y') }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label for="phone" class="block text-sm font-medium text-gray-600">Phone</label>
                        <input
                            id="phone"
                            name="phone"
                            type="text"
                            value="{{ old('phone', $original['phone']) }}"
                            placeholder="01X-XXX XXXX"
                            required
                            class="w-full rounded-xl border border-gray-200/80 bg-white/60 px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-gray-300 focus:bg-white focus:ring-2 focus:ring-black/5"
                        >
                        <p class="text-xs text-gray-400">Was: {{ $original['phone'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Address Section --}}
            <div class="form-section">
                <h2 class="form-section-title">Address</h2>
                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <label for="line_1" class="block text-sm font-medium text-gray-600">Address Line 1</label>
                        <input
                            id="line_1"
                            name="line_1"
                            type="text"
                            value="{{ old('line_1', $original['line_1']) }}"
                            placeholder="Street address"
                            required
                            class="w-full rounded-xl border border-gray-200/80 bg-white/60 px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-gray-300 focus:bg-white focus:ring-2 focus:ring-black/5"
                        >
                        <p class="text-xs text-gray-400">Was: {{ $original['line_1'] }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label for="line_2" class="block text-sm font-medium text-gray-600">Address Line 2</label>
                        <input
                            id="line_2"
                            name="line_2"
                            type="text"
                            value="{{ old('line_2', $original['line_2']) }}"
                            placeholder="Apartment, unit, etc. (optional)"
                            class="w-full rounded-xl border border-gray-200/80 bg-white/60 px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-gray-300 focus:bg-white focus:ring-2 focus:ring-black/5"
                        >
                        @if ($original['line_2'])
                            <p class="text-xs text-gray-400">Was: {{ $original['line_2'] }}</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label for="city" class="block text-sm font-medium text-gray-600">City</label>
                            <input
                                id="city"
                                name="city"
                                type="text"
                                value="{{ old('city', $original['city']) }}"
                                placeholder="City"
                                required
                                class="w-full rounded-xl border border-gray-200/80 bg-white/60 px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-gray-300 focus:bg-white focus:ring-2 focus:ring-black/5"
                            >
                            <p class="text-xs text-gray-400">Was: {{ $original['city'] }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label for="state" class="block text-sm font-medium text-gray-600">State</label>
                            <div class="relative">
                                <select
                                    id="state"
                                    name="state"
                                    required
                                    class="w-full rounded-xl border border-gray-200/80 bg-white/60 px-4 py-2.5 text-sm text-gray-900 focus:border-gray-300 focus:bg-white focus:ring-2 focus:ring-black/5 appearance-none"
                                >
                                    <option value="">Select state</option>
                                    @foreach ($states as $state)
                                        <option value="{{ $state }}" @selected(old('state', $original['state']) === $state)>
                                            {{ $state }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400">Was: {{ $original['state'] }}</p>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="postal_code" class="block text-sm font-medium text-gray-600">Postal Code</label>
                        <input
                            id="postal_code"
                            name="postal_code"
                            type="text"
                            value="{{ old('postal_code', $original['postal_code']) }}"
                            placeholder="5-digit postal code"
                            required
                            class="w-full rounded-xl border border-gray-200/80 bg-white/60 px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-gray-300 focus:bg-white focus:ring-2 focus:ring-black/5"
                        >
                        <p class="text-xs text-gray-400">Was: {{ $original['postal_code'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit">
                    Save Changes
                </x-button>
                <x-button href="{{ route('persons.show', $person) }}" variant="ghost">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-card>

</x-layouts.app>

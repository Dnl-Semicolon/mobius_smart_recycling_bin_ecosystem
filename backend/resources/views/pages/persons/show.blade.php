<x-layouts.app :title="$person->name">
    <x-slot:back>
        <x-back-button href="{{ route('persons.index') }}" />
    </x-slot:back>

    <x-slot:header>
        {{ $person->name }}
    </x-slot:header>

    <x-slot:actions>
        <x-button href="{{ route('persons.edit', $person) }}">
            Edit
        </x-button>
    </x-slot:actions>

    <div class="space-y-6">
        {{-- Person Details --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title">Personal Info</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-gray-400">Name</dt>
                        <dd class="text-gray-900">{{ $person->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Phone</dt>
                        <dd class="text-gray-900">{{ $person->phone }}</dd>
                    </div>
                    @if ($person->birthday)
                        <div>
                            <dt class="text-xs text-gray-400">Birthday</dt>
                            <dd class="text-gray-900">{{ $person->birthday->format('d F Y') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </x-card>

        {{-- Address --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title">Address</h2>
                @if ($person->addresses->isNotEmpty())
                    @php $address = $person->addresses->first(); @endphp
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs text-gray-400">Street</dt>
                            <dd class="text-gray-900">
                                {{ $address->line_1 }}
                                @if ($address->line_2)
                                    <br>{{ $address->line_2 }}
                                @endif
                            </dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-xs text-gray-400">City</dt>
                                <dd class="text-gray-900">{{ $address->city }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-400">State</dt>
                                <dd class="text-gray-900">{{ $address->state }}</dd>
                            </div>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Postal Code</dt>
                            <dd class="text-gray-900">{{ $address->postal_code }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="text-gray-400 text-sm">No address on file</p>
                @endif
            </div>

            {{-- Add Address (disabled for now) --}}
            <div class="mt-6 pt-6 border-t border-gray-100">
                <button
                    type="button"
                    disabled
                    class="pill text-sm text-gray-400 bg-gray-100 cursor-not-allowed"
                >
                    Add Another Address
                </button>
                <p class="text-xs text-gray-400 mt-2">Multiple addresses coming soon</p>
            </div>
        </x-card>

        <div class="flex justify-end">
            <form
                action="{{ route('persons.destroy', $person) }}"
                method="POST"
                hx-boost="false"
                onsubmit="return confirm('Delete this person? This cannot be undone.')"
            >
                @csrf
                @method('DELETE')

                <x-button type="submit" variant="danger">
                    Delete
                </x-button>
            </form>
        </div>
    </div>
</x-layouts.app>

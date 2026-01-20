<x-layouts.app title="Persons">
    <x-slot:back>
        <x-back-button href="{{ route('home') }}" />
    </x-slot:back>

    <x-slot:header>
        Persons
    </x-slot:header>

    <x-slot:actions>
        <x-button href="{{ route('persons.create') }}">
            Add
        </x-button>
    </x-slot:actions>

    @if ($people->isEmpty())
        {{-- Empty State --}}
        <x-card variant="subtle" class="text-center py-16">
            <p class="text-gray-400 mb-6">No persons yet</p>
            <x-button href="{{ route('persons.create') }}">
                Create your first person
            </x-button>
        </x-card>
    @else
        {{-- Person List --}}
        <div class="space-y-3">
            @foreach ($people as $person)
                <x-card :href="route('persons.show', $person)" :interactive="true" class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-gray-900 truncate">{{ $person->name }}</h2>
                            <p class="text-sm text-gray-500 mt-0.5">{{ $person->phone }}</p>
                            @if ($person->birthday)
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $person->birthday->format('d M Y') }}
                                </p>
                            @endif
                        </div>
                        @if ($person->addresses->isNotEmpty())
                            <span class="shrink-0 text-xs text-gray-500 bg-gray-100/80 rounded-full px-2.5 py-1">
                                {{ $person->addresses->first()->city }}
                            </span>
                        @endif
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
</x-layouts.app>

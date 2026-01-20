<x-layouts.app title="Home">
    <x-slot:actions>
        <x-button href="/persons">
            Persons
        </x-button>
    </x-slot:actions>

    <x-card class="p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Welcome to Mobius</h1>
        <p class="text-gray-500 mb-8">Demo playground for learning Laravel + Blade + Alpine.js</p>

        <div class="flex gap-3">
            <x-button href="/persons/create">
                Create Person
            </x-button>
            <x-button href="/persons" variant="secondary">
                View All
            </x-button>
        </div>
    </x-card>
</x-layouts.app>

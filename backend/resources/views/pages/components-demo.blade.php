<x-layouts.app title="Components">
    <x-slot:back>
        <x-back-button href="/" />
    </x-slot:back>

    <x-slot:header>
        Components
    </x-slot:header>

    <div class="space-y-6">
        <x-card class="p-8">
            <div class="space-y-3">
                <p class="text-sm font-medium text-gray-400">Component Demo</p>
                <h1 class="text-2xl font-bold text-gray-900">Review the building blocks</h1>
                <p class="text-gray-500">Preview the shared Blade components before wiring the UI.</p>
                <div class="flex flex-wrap gap-3 pt-2">
                    <x-button href="/" variant="primary">Primary</x-button>
                    <x-button href="/" variant="secondary">Secondary</x-button>
                    <x-button href="/" variant="ghost">Ghost</x-button>
                    <x-button href="/" variant="danger">Danger</x-button>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="space-y-4">
                <div>
                    <h2 class="font-semibold text-gray-900">Buttons</h2>
                    <p class="text-sm text-gray-500">Render as anchors when you pass href.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <x-button>Primary</x-button>
                    <x-button variant="secondary">Secondary</x-button>
                    <x-button variant="ghost">Ghost</x-button>
                    <x-button variant="danger">Danger</x-button>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="space-y-4">
                <div>
                    <h2 class="font-semibold text-gray-900">Inputs</h2>
                    <p class="text-sm text-gray-500">Error messages appear after validation failures.</p>
                </div>
                <div class="space-y-4">
                    <x-input
                        name="demo_name"
                        label="Full name"
                        placeholder="Nadia Aiman"
                        hint="Use the legal name from the ID card."
                    />
                    <x-input
                        name="demo_phone"
                        label="Phone"
                        placeholder="012-345 6789"
                        hint="Malaysian format only."
                    />
                    <x-select
                        name="demo_state"
                        label="State"
                        :options="['Selangor', 'KL', 'Penang', 'Johor']"
                        placeholder="Select state"
                    />
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="space-y-4">
                <div>
                    <h2 class="font-semibold text-gray-900">Cards</h2>
                    <p class="text-sm text-gray-500">Use the variant prop to switch styles.</p>
                </div>
                <div class="space-y-3">
                    <x-card variant="glass" class="p-4">
                        <p class="text-sm font-semibold text-gray-900">Glass</p>
                        <p class="text-xs text-gray-500">Default glass effect.</p>
                    </x-card>
                    <x-card variant="subtle" class="p-4">
                        <p class="text-sm font-semibold text-gray-900">Subtle</p>
                        <p class="text-xs text-gray-500">Softer backdrop.</p>
                    </x-card>
                    <x-card variant="plain" class="p-4">
                        <p class="text-sm font-semibold text-gray-900">Plain</p>
                        <p class="text-xs text-gray-500">Solid white base.</p>
                    </x-card>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="space-y-4">
                <div>
                    <h2 class="font-semibold text-gray-900">Interactive Cards</h2>
                    <p class="text-sm text-gray-500">Cards with href become clickable links.</p>
                </div>
                <x-card href="/components" :interactive="true" class="p-4">
                    <p class="text-sm font-semibold text-gray-900">Click me</p>
                    <p class="text-xs text-gray-500">I lift on hover and have a focus ring.</p>
                </x-card>
            </div>
        </x-card>
    </div>
</x-layouts.app>

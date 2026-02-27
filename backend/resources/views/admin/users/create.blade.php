<x-layouts.admin title="Create User">
    <x-slot:back>
        <x-back-button href="{{ route('admin.users.index') }}" />
    </x-slot:back>

    <x-slot:header>
        Create User
    </x-slot:header>

    <x-card>
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-8">
            @csrf

            <div class="form-section">
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-user class="w-3.5 h-3.5" />
                    Account Details
                </h2>
                <div class="space-y-4">
                    <x-input
                        name="name"
                        label="Full Name"
                        placeholder="e.g. John Smith"
                        required
                    />

                    <x-input
                        name="email"
                        label="Email Address"
                        type="email"
                        placeholder="e.g. john@mobius.test"
                        required
                    />

                    <x-input
                        name="password"
                        label="Password"
                        type="password"
                        hint="Minimum 8 characters"
                        required
                    />

                    <x-select
                        name="role"
                        label="Role"
                        :options="collect($roles)->mapWithKeys(fn($r) => [$r->value => ucfirst(str_replace('_', ' ', $r->value))])->toArray()"
                        placeholder="Select role"
                    />
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit">
                    <x-heroicon-o-check class="w-4 h-4" />
                    Create User
                </x-button>
                <x-button href="{{ route('admin.users.index') }}" variant="ghost">
                    Cancel
                </x-button>
            </div>
        </form>
    </x-card>
</x-layouts.admin>

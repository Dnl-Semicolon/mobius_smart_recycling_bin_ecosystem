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

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Roles</label>
                        <div class="flex flex-wrap gap-3">
                            @foreach ($roles as $role)
                                <label class="relative flex items-center gap-2 cursor-pointer select-none">
                                    <input
                                        type="checkbox"
                                        name="roles[]"
                                        value="{{ $role->value }}"
                                        class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-200"
                                        @checked(is_array(old('roles')) && in_array($role->value, old('roles')))
                                    >
                                    <span class="text-sm text-gray-700">{{ $role->label() }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('roles')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">Select at least one role. Users with multiple roles can switch between them.</p>
                    </div>
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

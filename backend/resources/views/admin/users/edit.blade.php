<x-layouts.admin :title="'Edit ' . $user->name">
    <x-slot:back>
        <x-back-button href="{{ route('admin.users.index') }}" />
    </x-slot:back>

    <x-slot:header>
        Edit User
    </x-slot:header>

    <div class="space-y-6">
        <x-card>
            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <div class="form-section">
                    <h2 class="form-section-title flex items-center gap-1.5">
                        <x-heroicon-o-user class="w-3.5 h-3.5" />
                        Account Details
                    </h2>
                    <div class="space-y-4">
                        <x-input
                            name="name"
                            label="Full Name"
                            :value="old('name', $user->name)"
                            required
                        />

                        <x-input
                            name="email"
                            label="Email Address"
                            type="email"
                            :value="old('email', $user->email)"
                            required
                        />

                        <x-input
                            name="password"
                            label="New Password"
                            type="password"
                            hint="Leave blank to keep current password"
                        />

                        <x-select
                            name="role"
                            label="Role"
                            :options="collect($roles)->mapWithKeys(fn($r) => [$r->value => ucfirst(str_replace('_', ' ', $r->value))])->toArray()"
                            :value="old('role', $user->role->value)"
                        />

                        <div class="pt-2">
                            <span class="text-xs text-gray-400 uppercase tracking-wide">Member Since</span>
                            <p class="text-sm text-gray-700 mt-0.5">{{ $user->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-button type="submit">
                        <x-heroicon-o-check class="w-4 h-4" />
                        Update User
                    </x-button>
                    <x-button href="{{ route('admin.users.index') }}" variant="ghost">
                        Cancel
                    </x-button>
                </div>
            </form>
        </x-card>

        {{-- Delete Action --}}
        @if ($user->id !== auth()->id())
            <div class="flex justify-end">
                <form
                    action="{{ route('admin.users.destroy', $user) }}"
                    method="POST"
                    hx-boost="false"
                    onsubmit="return confirm('Delete this user? This cannot be undone.')"
                >
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger">
                        <x-heroicon-o-trash class="w-4 h-4" />
                        Delete User
                    </x-button>
                </form>
            </div>
        @endif
    </div>
</x-layouts.admin>

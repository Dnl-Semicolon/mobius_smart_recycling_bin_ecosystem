{{-- Flash Messages --}}
@if (session('success'))
    <div class="mb-6 flex items-center gap-2 rounded-xl bg-emerald-50 border border-emerald-200/60 px-4 py-3 text-sm text-emerald-700">
        <x-heroicon-s-check-circle class="w-5 h-5 shrink-0" />
        {{ session('success') }}
    </div>
@endif

<div class="space-y-6">
    {{-- Section 1: Profile Information --}}
    <x-card>
        <form action="{{ $updateRoute }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="form-section">
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-user class="w-3.5 h-3.5" />
                    Profile Information
                </h2>

                <div class="space-y-4">
                    <x-input
                        name="name"
                        label="Name"
                        :value="old('name', $user->name)"
                        required
                    />

                    <x-input
                        name="email"
                        label="Email"
                        type="email"
                        :value="old('email', $user->email)"
                        required
                    />

                    {{-- Read-only info --}}
                    <div class="flex items-center gap-4 pt-2">
                        <div>
                            <span class="text-xs text-gray-400 uppercase tracking-wide">Role</span>
                            <p class="mt-0.5">
                                <span class="text-sm font-medium bg-emerald-100 text-emerald-700 rounded-full px-3 py-1">
                                    {{ $user->primaryRole()->label() }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 uppercase tracking-wide">Member Since</span>
                            <p class="mt-0.5 text-sm text-gray-700">{{ $user->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit">
                    <x-heroicon-o-check class="w-4 h-4" />
                    Save Changes
                </x-button>
            </div>
        </form>
    </x-card>

    {{-- Section 2: Change Password --}}
    <x-card>
        <form action="{{ $passwordRoute }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="form-section">
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-lock-closed class="w-3.5 h-3.5" />
                    Change Password
                </h2>

                <div class="space-y-4">
                    <x-input
                        name="current_password"
                        label="Current Password"
                        type="password"
                        required
                    />

                    <x-input
                        name="password"
                        label="New Password"
                        type="password"
                        hint="Minimum 8 characters"
                        required
                    />

                    <x-input
                        name="password_confirmation"
                        label="Confirm New Password"
                        type="password"
                        required
                    />
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit">
                    <x-heroicon-o-key class="w-4 h-4" />
                    Update Password
                </x-button>
            </div>
        </form>
    </x-card>
</div>

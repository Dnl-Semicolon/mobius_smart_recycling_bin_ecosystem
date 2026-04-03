@php
    $currentRoute = \Illuminate\Support\Facades\Route::currentRouteName() ?? '';
    $routePrefix = preg_replace('/\.profile\.\w+$/', '', $currentRoute);
    if (empty($routePrefix)) {
        $routePrefix = explode('/', trim(request()->path(), '/'))[0] ?? 'collector';
    }
@endphp

{{-- Flash Messages --}}
@if (session('success'))
    <div class="mb-6 flex items-center gap-2 rounded-xl bg-emerald-50 border border-emerald-200/60 px-4 py-3 text-sm text-emerald-700">
        <x-heroicon-s-check-circle class="w-5 h-5 shrink-0" />
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-6 flex items-center gap-2 rounded-xl bg-red-50 border border-red-200/60 px-4 py-3 text-sm text-red-700">
        <x-heroicon-s-x-circle class="w-5 h-5 shrink-0" />
        {{ session('error') }}
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

    {{-- Section 2: Phone Verification --}}
    <x-card>
        <div class="form-section">
            <h2 class="form-section-title flex items-center gap-1.5">
                <x-heroicon-o-device-phone-mobile class="w-3.5 h-3.5" />
                Phone Verification
            </h2>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-700">{{ $user->phone ?: 'No phone number set' }}</p>
                    </div>
                    @if ($user->phone_verified_at)
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 bg-emerald-50 rounded-full px-2.5 py-1 border border-emerald-100">
                            <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                            Verified
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-600 bg-amber-50 rounded-full px-2.5 py-1 border border-amber-100">
                            <x-heroicon-s-exclamation-circle class="w-3.5 h-3.5" />
                            Unverified
                        </span>
                    @endif
                </div>

                @unless ($user->phone_verified_at)
                    <div x-data="{ showOtp: {{ session('otp_code') ? 'true' : 'false' }} }" class="space-y-3">
                        <form method="POST" action="{{ route($routePrefix . '.profile.send-otp') }}" class="flex items-end gap-2">
                            @csrf
                            <div class="flex-1">
                                <x-input
                                    name="phone"
                                    label="Phone Number"
                                    :value="old('phone', $user->phone ?? '')"
                                    placeholder="012-345 6789"
                                    required
                                />
                            </div>
                            <x-button type="submit" class="mb-0.5">
                                Send OTP
                            </x-button>
                        </form>

                        @if (session('otp_code'))
                            <div class="rounded-lg bg-blue-50 border border-blue-200 px-3 py-2">
                                <p class="text-sm text-blue-700">Your OTP code: <span class="font-mono font-bold">{{ session('otp_code') }}</span></p>
                            </div>
                        @endif

                        <div x-show="showOtp" x-transition>
                            <form method="POST" action="{{ route($routePrefix . '.profile.verify-otp') }}" class="flex items-end gap-2">
                                @csrf
                                <div class="flex-1">
                                    <x-input
                                        name="otp"
                                        label="Enter OTP"
                                        placeholder="6-digit code"
                                        maxlength="6"
                                        required
                                    />
                                </div>
                                <x-button type="submit" class="mb-0.5">
                                    Verify
                                </x-button>
                            </form>
                        </div>
                    </div>
                @endunless
            </div>
        </div>
    </x-card>

    {{-- Section 3: Change Password --}}
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

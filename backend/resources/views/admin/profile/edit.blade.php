<x-layouts.admin title="Profile Settings">
    <x-slot:header>
        Settings
    </x-slot:header>

    <div
        x-data="{
            activeTab: @js($tab),
            init() {
                window.addEventListener('popstate', () => {
                    const params = new URLSearchParams(window.location.search);
                    this.activeTab = params.get('tab') || 'profile';
                });
            }
        }"
        x-effect="
            const url = new URL(window.location);
            if (activeTab === 'profile') {
                url.searchParams.delete('tab');
            } else {
                url.searchParams.set('tab', activeTab);
            }
            if (url.toString() !== window.location.toString()) {
                history.pushState({}, '', url);
            }
        "
        class="max-w-5xl mx-auto flex flex-col lg:flex-row gap-8"
    >
        {{-- Settings sidebar (GitHub-style) --}}
        <nav class="lg:w-56 shrink-0">
            <ul class="flex lg:flex-col gap-1 overflow-x-auto lg:overflow-visible pb-2 lg:pb-0 -mx-4 px-4 lg:mx-0 lg:px-0">
                <li>
                    <button @click="activeTab = 'profile'" type="button"
                        :class="activeTab === 'profile'
                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-transparent'"
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors border cursor-pointer">
                        <x-heroicon-o-user class="w-4 h-4 shrink-0" />
                        Public profile
                    </button>
                </li>
                <li>
                    <button @click="activeTab = 'account'" type="button"
                        :class="activeTab === 'account'
                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-transparent'"
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors border cursor-pointer">
                        <x-heroicon-o-cog-6-tooth class="w-4 h-4 shrink-0" />
                        Account
                    </button>
                </li>
                <li>
                    <button @click="activeTab = 'password'" type="button"
                        :class="activeTab === 'password'
                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-transparent'"
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors border cursor-pointer">
                        <x-heroicon-o-lock-closed class="w-4 h-4 shrink-0" />
                        Password
                    </button>
                </li>
            </ul>
        </nav>

        {{-- Main content --}}
        <div class="flex-1 min-w-0">
            {{-- PUBLIC PROFILE --}}
            <section x-show="activeTab === 'profile'" x-cloak>
                <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="email" value="{{ $user->email }}">

                    <h2 class="text-xl font-semibold text-gray-900 pb-4 border-b border-gray-200/80">Public profile</h2>

                    <div class="mt-6 flex flex-col-reverse lg:flex-row gap-8">
                        <div class="flex-1 space-y-5">
                            {{-- Name --}}
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                    class="w-full rounded-lg border border-gray-300 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:bg-white focus:ring-1 focus:ring-emerald-500 transition-colors">
                                <p class="mt-1.5 text-xs text-gray-500">Your name as it appears across Mobius.</p>
                                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Username --}}
                            <div
                                x-data="{
                                    username: @js(old('username', $user->username ?? '')),
                                    checking: false, available: null, message: '', timer: null,
                                    original: @js($user->username ?? ''),
                                    check() {
                                        clearTimeout(this.timer);
                                        if (!this.username || this.username.length < 3) { this.available = null; this.message = ''; return; }
                                        if (this.username === this.original) { this.available = true; this.message = 'This is your current username.'; return; }
                                        if (!/^[a-z0-9_]+$/.test(this.username)) { this.available = false; this.message = 'Only lowercase letters, numbers, and underscores.'; return; }
                                        this.checking = true;
                                        this.timer = setTimeout(() => {
                                            fetch('/api/v1/check-username?username=' + encodeURIComponent(this.username) + '&except={{ $user->id }}')
                                                .then(r => r.json())
                                                .then(data => { this.available = data.available; this.message = data.message; this.checking = false; })
                                                .catch(() => { this.checking = false; });
                                        }, 500);
                                    }
                                }"
                                x-init="if (username && username.length >= 3) check()"
                            >
                                <label for="username" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-700 mb-1.5">
                                    Username
                                    <span class="relative group">
                                        <x-heroicon-o-question-mark-circle class="w-3.5 h-3.5 text-gray-400 cursor-help" />
                                        <span class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 hidden group-hover:block w-52 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg z-10">
                                            Your unique handle on Mobius. Visible on public-facing pages.
                                        </span>
                                    </span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400 select-none">@</span>
                                    <input type="text" name="username" id="username" x-model="username"
                                        @input="username = username.toLowerCase().replace(/[^a-z0-9_]/g, ''); check()"
                                        placeholder="your_username"
                                        class="w-full rounded-lg border border-gray-300 bg-gray-50/50 pl-7 pr-9 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:bg-white focus:ring-1 focus:ring-emerald-500 transition-colors">
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <template x-if="checking"><svg class="w-4 h-4 text-gray-400 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg></template>
                                        <template x-if="!checking && available === true"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg></template>
                                        <template x-if="!checking && available === false"><svg class="w-4 h-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></template>
                                    </div>
                                </div>
                                <p x-show="message" x-text="message" :class="available === false ? 'text-red-500' : 'text-gray-500'" class="mt-1.5 text-xs transition-colors"></p>
                                <p x-show="!message" class="mt-1.5 text-xs text-gray-500">Optional. Shown instead of your name on public-facing pages.</p>
                                @error('username') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Bio --}}
                            <div x-data="{ count: 0 }" x-init="count = $refs.bio.value.length">
                                <label for="bio" class="block text-sm font-medium text-gray-700 mb-1.5">Bio</label>
                                <textarea name="bio" id="bio" x-ref="bio" rows="3" maxlength="500" @input="count = $refs.bio.value.length"
                                    placeholder="Tell us a little bit about yourself"
                                    class="w-full rounded-lg border border-gray-300 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:bg-white focus:ring-1 focus:ring-emerald-500 transition-colors resize-none"
                                >{{ old('bio', $user->bio) }}</textarea>
                                <div class="mt-1.5 flex justify-between">
                                    <span class="text-xs text-gray-500">A short description visible on your profile.</span>
                                    <span class="text-xs text-gray-400" x-text="count + ' / 500'"></span>
                                </div>
                                @error('bio') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Phone with Malaysian masking --}}
                            <div
                                x-data="{
                                    phone: @js(old('phone', $user->phone ?? '')),
                                    valid: null,
                                    serverError: @js($errors->has('phone')),
                                    format() {
                                        this.serverError = false;
                                        let digits = this.phone.replace(/[^\d]/g, '');
                                        if (digits.length === 0) { this.valid = null; return; }
                                        if (digits.length >= 3 && digits.startsWith('01')) {
                                            let prefix = digits.slice(0, 3), rest = digits.slice(3);
                                            if (rest.length <= 3) { this.phone = prefix + '-' + rest; }
                                            else if (rest.length <= 7) { this.phone = prefix + '-' + rest.slice(0, 3) + ' ' + rest.slice(3); }
                                            else { rest = rest.slice(0, 8); this.phone = prefix + '-' + rest.slice(0, 4) + ' ' + rest.slice(4); }
                                        } else if (digits.length >= 2 && digits.startsWith('0')) {
                                            let prefix = digits.slice(0, 2), rest = digits.slice(2);
                                            if (rest.length <= 4) { this.phone = prefix + '-' + rest; }
                                            else { rest = rest.slice(0, 8); this.phone = prefix + '-' + rest.slice(0, 4) + ' ' + rest.slice(4); }
                                        }
                                        this.valid = /^0\d[\d\s\-]{6,12}$/.test(this.phone);
                                    }
                                }"
                                x-init="if (phone && !serverError) format()"
                            >
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute left-0 top-0 bottom-0 flex items-center pl-3 gap-1.5 border-r border-gray-200 pr-2.5">
                                        <img src="{{ asset('images/flag-malaysia.svg') }}" alt="Malaysia" class="w-5 h-3.5 rounded-[2px] shadow-sm ring-1 ring-black/10 object-cover">
                                        <span class="text-xs text-gray-400 font-medium">+60</span>
                                    </div>
                                    <input type="text" name="phone" id="phone" x-model="phone" @input="format()" placeholder="012-345 6789"
                                        class="w-full rounded-lg border border-gray-300 bg-gray-50/50 pl-[6.5rem] pr-9 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:bg-white focus:ring-1 focus:ring-emerald-500 transition-colors">
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <template x-if="!serverError && valid === true"><svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg></template>
                                        <template x-if="serverError || valid === false"><svg class="w-4 h-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></template>
                                    </div>
                                </div>
                                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Right: avatar --}}
                        <div class="lg:w-52 shrink-0"
                            x-data="{
                                hasAvatar: @js((bool) $user->avatar_path),
                                previewUrl: @js($user->avatar_path ? Storage::url($user->avatar_path) : ''),
                                handleFile(event) {
                                    const file = event.target.files[0];
                                    if (!file) return;
                                    if (file.size > 2 * 1024 * 1024) { alert('Image must be less than 2MB'); event.target.value = ''; return; }
                                    const reader = new FileReader();
                                    reader.onload = (e) => { this.previewUrl = e.target.result; this.hasAvatar = true; };
                                    reader.readAsDataURL(file);
                                },
                                removeAvatar() {
                                    if (!confirm('Remove your profile picture?')) return;
                                    fetch('{{ route('admin.profile.avatar.remove') }}', {
                                        method: 'DELETE',
                                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                                    }).then(() => window.location.reload());
                                }
                            }"
                        >
                            <label class="block text-sm font-medium text-gray-700 mb-3">Profile picture</label>
                            <div class="relative group">
                                <div class="w-44 h-44 rounded-full overflow-hidden border-2 border-gray-200 bg-gray-100 mx-auto lg:mx-0">
                                    <img x-show="hasAvatar" :src="previewUrl" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                    <div x-show="!hasAvatar" class="w-full h-full flex items-center justify-center bg-emerald-50">
                                        <span class="text-4xl font-bold text-emerald-600">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                    </div>
                                </div>
                                <button type="button" @click="$refs.avatarInput.click()"
                                    class="absolute inset-0 w-44 h-44 rounded-full mx-auto lg:mx-0 flex items-center justify-center bg-black/0 group-hover:bg-black/40 transition-colors cursor-pointer">
                                    <x-heroicon-o-camera class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                                </button>
                            </div>
                            <input type="file" name="avatar" x-ref="avatarInput" @change="handleFile($event)" accept="image/*" class="hidden">
                            <div class="flex items-center gap-2 mt-3 justify-center lg:justify-start">
                                <button type="button" @click="$refs.avatarInput.click()" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors cursor-pointer">Upload photo</button>
                                @if ($user->avatar_path)
                                    <span class="text-gray-300">|</span>
                                    <button type="button" @click="removeAvatar()" class="text-xs font-medium text-red-500 hover:text-red-600 transition-colors cursor-pointer">Remove</button>
                                @endif
                            </div>
                            @error('avatar') <p class="mt-1 text-xs text-red-600 text-center lg:text-left">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-8 pt-5 border-t border-gray-200/80">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">
                            <x-heroicon-o-check class="w-4 h-4 opacity-80" />
                            Update profile
                        </button>
                    </div>
                </form>
            </section>

            {{-- ACCOUNT --}}
            <section x-show="activeTab === 'account'" x-cloak>
                <h2 class="text-xl font-semibold text-gray-900 pb-4 border-b border-gray-200/80">Account</h2>

                <div class="mt-6 space-y-4 max-w-lg">
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                    <x-heroicon-o-envelope class="w-4.5 h-4.5 text-gray-500" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Email address</p>
                                    <p class="text-sm text-gray-600 mt-0.5">{{ $user->email }}</p>
                                </div>
                            </div>
                            @if ($user->hasVerifiedEmail())
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 bg-emerald-50 rounded-full px-2.5 py-1 border border-emerald-100 shrink-0">
                                    <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                                    Verified
                                </span>
                            @else
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-600 bg-amber-50 rounded-full px-2.5 py-1 border border-amber-100">
                                        <x-heroicon-s-exclamation-circle class="w-3.5 h-3.5" />
                                        Unverified
                                    </span>
                                    <form method="POST" action="{{ route('verification.send') }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors">Resend</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <p class="mt-2 text-xs text-gray-500 pl-12">To change your email, update it in the profile section.</p>
                    </div>

                    {{-- Phone Verification --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                    <x-heroicon-o-device-phone-mobile class="w-4.5 h-4.5 text-gray-500" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Phone number</p>
                                    <p class="text-sm text-gray-600 mt-0.5">{{ $user->phone ?: 'Not set' }}</p>
                                </div>
                            </div>
                            @if ($user->phone_verified_at)
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 bg-emerald-50 rounded-full px-2.5 py-1 border border-emerald-100 shrink-0">
                                    <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                                    Verified
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-600 bg-amber-50 rounded-full px-2.5 py-1 border border-amber-100 shrink-0">
                                    <x-heroicon-s-exclamation-circle class="w-3.5 h-3.5" />
                                    Unverified
                                </span>
                            @endif
                        </div>

                        @unless ($user->phone_verified_at)
                            <div class="mt-3 pl-12 space-y-3" x-data="{ showOtp: {{ session('otp_code') ? 'true' : 'false' }} }">
                                {{-- Send OTP form --}}
                                <form method="POST" action="{{ route('admin.profile.send-otp') }}" class="flex items-end gap-2">
                                    @csrf
                                    <div class="flex-1">
                                        <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="012-345 6789" required
                                            class="w-full rounded-lg border border-gray-300 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:bg-white focus:ring-1 focus:ring-emerald-500 transition-colors">
                                    </div>
                                    <button type="submit" class="shrink-0 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                                        Send OTP
                                    </button>
                                </form>

                                @if (session('otp_code'))
                                    <div class="rounded-lg bg-blue-50 border border-blue-200 px-3 py-2">
                                        <p class="text-sm text-blue-700">Your OTP code: <span class="font-mono font-bold">{{ session('otp_code') }}</span></p>
                                    </div>
                                @endif

                                {{-- Verify OTP form --}}
                                <div x-show="showOtp" x-transition>
                                    <form method="POST" action="{{ route('admin.profile.verify-otp') }}" class="flex items-end gap-2">
                                        @csrf
                                        <div class="flex-1">
                                            <input type="text" name="otp" maxlength="6" placeholder="Enter 6-digit code" required
                                                class="w-full rounded-lg border border-gray-300 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:bg-white focus:ring-1 focus:ring-emerald-500 transition-colors font-mono tracking-widest text-center">
                                        </div>
                                        <button type="submit" class="shrink-0 rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800 transition-colors">
                                            Verify
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endunless
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                <x-heroicon-o-shield-check class="w-4.5 h-4.5 text-gray-500" />
                            </div>
                            <p class="text-sm font-medium text-gray-900">Roles</p>
                        </div>
                        <div class="flex flex-wrap gap-2 pl-12">
                            @php
                                $roleColors = [
                                    'admin' => 'bg-violet-50 text-violet-700 border-violet-200',
                                    'collector' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'public_user' => 'bg-gray-50 text-gray-700 border-gray-200',
                                    'store_owner' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'agency_admin' => 'bg-teal-50 text-teal-700 border-teal-200',
                                ];
                            @endphp
                            @foreach ($user->getRolesArray() as $role)
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium rounded-full px-3 py-1 border {{ $roleColors[$role] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">
                                    {{ ucfirst(str_replace('_', ' ', $role)) }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                <x-heroicon-o-calendar-days class="w-4.5 h-4.5 text-gray-500" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Member since</p>
                                <p class="text-sm text-gray-600 mt-0.5">{{ $user->created_at->format('F j, Y') }} <span class="text-gray-400">&middot;</span> <span class="text-gray-400">{{ $user->created_at->diffForHumans() }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- PASSWORD --}}
            <section x-show="activeTab === 'password'" x-cloak>
                <form action="{{ route('admin.profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h2 class="text-xl font-semibold text-gray-900 pb-4 border-b border-gray-200/80">Change password</h2>

                    <div class="mt-6 space-y-5 max-w-lg">
                        <div x-data="{ show: false }">
                            <label for="current_password" class="block text-sm font-medium text-gray-600 mb-1.5">Current password</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" name="current_password" id="current_password" required
                                    class="w-full rounded-xl border border-gray-200/80 bg-white/60 px-4 pr-10 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10">
                                <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
                                    <x-heroicon-o-eye x-show="!show" class="w-4 h-4" />
                                    <x-heroicon-o-eye-slash x-show="show" x-cloak class="w-4 h-4" />
                                </button>
                            </div>
                            @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <x-password-strength label="New Password" :confirm="true" confirmLabel="Confirm New Password" />
                    </div>

                    <div class="mt-8 pt-5 border-t border-gray-200/80">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">
                            <x-heroicon-o-lock-closed class="w-4 h-4 opacity-80" />
                            Update password
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-layouts.admin>

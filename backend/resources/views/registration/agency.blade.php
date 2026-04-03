<x-layouts.app title="Register as Collection Agency">
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 via-white to-emerald-50/30 px-4 py-12">
        <div class="w-full max-w-lg">

            {{-- Header --}}
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-block">
                    <img src="{{ asset('images/mobius-icon.png') }}" alt="Mobius" class="w-14 h-14 object-contain mx-auto mb-3">
                </a>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Register as Collection Agency</h1>
                <p class="text-sm text-gray-500 mt-1.5">Join the Mobius recycling ecosystem as a collection partner</p>
            </div>

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                    <p class="text-sm font-semibold text-red-700 mb-1">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-sm text-red-600 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('registration.agency.store') }}" class="space-y-5">
                @csrf

                {{-- Company Name --}}
                <div>
                    <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1.5">Company Name <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="company_name"
                        name="company_name"
                        value="{{ old('company_name') }}"
                        placeholder="e.g. CleanCycle Sdn Bhd"
                        required
                        autofocus
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/10 focus:outline-none transition-all"
                    >
                    @error('company_name')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contact Name --}}
                <div>
                    <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1.5">Contact Person <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="contact_name"
                        name="contact_name"
                        value="{{ old('contact_name') }}"
                        placeholder="Your full name"
                        required
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/10 focus:outline-none transition-all"
                    >
                    @error('contact_name')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contact Email --}}
                <div>
                    <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input
                        type="email"
                        id="contact_email"
                        name="contact_email"
                        value="{{ old('contact_email') }}"
                        placeholder="you@company.com"
                        required
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/10 focus:outline-none transition-all"
                    >
                    @error('contact_email')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contact Phone (Malaysian format) --}}
                <div x-data="{
                    phone: '{{ old('contact_phone', '+60') }}',
                    format(e) {
                        let v = this.phone.replace(/[^\d+]/g, '');
                        if (!v.startsWith('+60')) v = '+60' + v.replace(/^\+?6?0?/, '');
                        if (v.length > 4) v = v.slice(0,3) + ' ' + v.slice(3);
                        if (v.length > 7) v = v.slice(0,7) + '-' + v.slice(7);
                        if (v.length > 12) v = v.slice(0,12) + ' ' + v.slice(12);
                        this.phone = v.slice(0, 16);
                    }
                }">
                    <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1.5">Phone <span class="text-red-500">*</span></label>
                    <input
                        type="tel"
                        id="contact_phone"
                        name="contact_phone"
                        x-model="phone"
                        @input="format($event)"
                        placeholder="+60 12-345 6789"
                        required
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/10 focus:outline-none transition-all"
                    >
                    @error('contact_phone')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Type --}}
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1.5">Organisation Type <span class="text-red-500">*</span></label>
                    <select
                        id="type"
                        name="type"
                        required
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/10 focus:outline-none transition-all"
                    >
                        <option value="" disabled {{ old('type') ? '' : 'selected' }}>Select type...</option>
                        <option value="beverage_company" {{ old('type') === 'beverage_company' ? 'selected' : '' }}>Beverage Company</option>
                        <option value="recycling_company" {{ old('type') === 'recycling_company' ? 'selected' : '' }}>Recycling Company</option>
                        <option value="government" {{ old('type') === 'government' ? 'selected' : '' }}>Government</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description <span class="text-red-500">*</span></label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        placeholder="Tell us about your agency and your collection capabilities"
                        required
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/10 focus:outline-none transition-all resize-none"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition-colors shadow-sm"
                >
                    Submit Application
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Already have an account?
                <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">Sign in</a>
                &middot;
                <a href="{{ route('home') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">Back to home</a>
            </p>
        </div>
    </div>
</x-layouts.app>

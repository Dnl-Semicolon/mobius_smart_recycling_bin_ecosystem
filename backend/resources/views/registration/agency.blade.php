<x-layouts.app title="Collector Agency Registration">
    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 py-12">
        <div class="w-full max-w-lg">
            <div class="text-center mb-8">
                <img src="{{ asset('images/mobius-icon.png') }}" alt="Mobius" class="w-16 h-16 object-contain mx-auto mb-3">
                <h1 class="text-xl font-semibold text-gray-900">Collector Agency Registration</h1>
                <p class="text-sm text-gray-500 mt-1">Register your collection agency to join the platform</p>
            </div>

            <x-card class="p-6">
                <form method="POST" action="{{ route('registration.agency.store') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    {{-- Company Info --}}
                    <fieldset class="space-y-4">
                        <legend class="text-sm font-semibold text-gray-700 border-b border-gray-200 pb-2 w-full">Company Information</legend>

                        <x-input
                            name="name"
                            label="Company Name"
                            placeholder="e.g. CleanCycle Sdn Bhd"
                            required
                            autofocus
                        />

                        <x-input
                            name="description"
                            label="Description"
                            placeholder="Brief description of your services"
                        />

                        <div class="grid grid-cols-2 gap-4">
                            <x-input
                                name="fleet_size"
                                type="number"
                                label="Fleet Size"
                                placeholder="Number of vehicles"
                                min="1"
                            />

                            <x-input
                                name="coverage_area"
                                label="Coverage Area"
                                placeholder="e.g. Penang Island"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company Logo</label>
                            <input
                                type="file"
                                name="logo"
                                accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 file:cursor-pointer"
                            >
                            @error('logo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </fieldset>

                    {{-- Contact Details --}}
                    <fieldset class="space-y-4">
                        <legend class="text-sm font-semibold text-gray-700 border-b border-gray-200 pb-2 w-full">Contact Details</legend>

                        <x-input
                            name="contact_person"
                            label="Contact Person"
                            placeholder="Your full name"
                            required
                        />

                        <x-input
                            name="phone"
                            type="tel"
                            label="Phone"
                            placeholder="+60 12-345 6789"
                        />
                    </fieldset>

                    {{-- Account --}}
                    <fieldset class="space-y-4">
                        <legend class="text-sm font-semibold text-gray-700 border-b border-gray-200 pb-2 w-full">Account Credentials</legend>

                        <x-input
                            name="email"
                            type="email"
                            label="Email"
                            placeholder="you@company.com"
                            required
                        />

                        <x-input
                            name="password"
                            type="password"
                            label="Password"
                            placeholder="Min 8 characters"
                            required
                        />

                        <x-input
                            name="password_confirmation"
                            type="password"
                            label="Confirm Password"
                            placeholder="Repeat your password"
                            required
                        />
                    </fieldset>

                    <x-button type="submit" class="w-full justify-center">
                        <x-heroicon-o-paper-airplane class="w-4 h-4" />
                        Submit Application
                    </x-button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-4">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">Sign in</a>
                    &middot;
                    <a href="{{ route('home') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">Back</a>
                </p>
            </x-card>
        </div>
    </div>
</x-layouts.app>

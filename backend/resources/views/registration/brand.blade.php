<x-layouts.app title="Brand Registration">
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 via-white to-emerald-50/30 px-4 py-12">
        <div class="w-full max-w-lg" x-data="brandRegistration()" x-cloak>

            {{-- Header --}}
            <div class="text-center mb-8">
                <img src="{{ asset('images/mobius-icon.png') }}" alt="Mobius" class="w-14 h-14 object-contain mx-auto mb-3">
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Partner with Mobius</h1>
                <p class="text-sm text-gray-500 mt-1.5">Join our recycling ecosystem as a brand partner</p>
            </div>

            <form method="POST" action="{{ route('registration.brand.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="flow" :value="flow">
                <input type="hidden" name="brand_id" :value="selectedBrand ? selectedBrand.id : ''">

                {{-- Step 1: Brand Selection --}}
                <x-card class="p-6 mb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold text-gray-700">
                            <span x-show="flow === 'claim'">Select Your Brand</span>
                            <span x-show="flow === 'new'">New Brand Details</span>
                        </h2>
                        <button
                            type="button"
                            x-show="flow === 'new'"
                            @click="switchToClaim()"
                            class="text-xs text-emerald-600 hover:text-emerald-700 font-medium cursor-pointer"
                        >
                            &larr; Back to search
                        </button>
                    </div>

                    {{-- Claim flow: Search --}}
                    <div x-show="flow === 'claim'" class="space-y-3">
                        {{-- Search input --}}
                        <div class="relative" @click.outside="showDropdown = false">
                            <div class="relative">
                                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                                <input
                                    type="search"
                                    x-model="searchQuery"
                                    @input.debounce.300ms="searchBrands()"
                                    @focus="showDropdown = true; if (!brands.length && !searchQuery) searchBrands()"
                                    placeholder="Search for your brand (e.g. Starbucks, Tealive)..."
                                    class="w-full rounded-xl border border-gray-200/80 bg-white/60 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 focus:outline-none transition-all"
                                    x-ref="searchInput"
                                    autocomplete="off"
                                    data-form-type="other"
                                >
                                <div x-show="loading" class="absolute right-3.5 top-1/2 -translate-y-1/2">
                                    <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </div>
                            </div>

                            {{-- Dropdown results --}}
                            <div
                                x-show="showDropdown && !selectedBrand"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-1"
                                class="absolute z-20 mt-1.5 w-full bg-white border border-gray-200 rounded-xl shadow-lg shadow-black/8 max-h-64 overflow-y-auto"
                            >
                                <template x-if="brands.length === 0 && !loading">
                                    <div class="px-4 py-8 text-center">
                                        <p class="text-sm text-gray-400" x-text="searchQuery ? 'No brands match your search' : 'Type to search brands...'"></p>
                                    </div>
                                </template>

                                <template x-for="brand in brands" :key="brand.id">
                                    <button
                                        type="button"
                                        @click="selectBrand(brand)"
                                        class="w-full flex items-center gap-3 px-4 py-3 hover:bg-emerald-50/60 transition-colors text-left cursor-pointer border-b border-gray-100 last:border-0"
                                    >
                                        <span
                                            class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center text-white text-[10px] font-bold shadow-sm"
                                            :style="`background: ${brand.primary_color || '#6B7280'}`"
                                            x-text="brand.name.substring(0, 2).toUpperCase()"
                                        ></span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate" x-text="brand.name"></p>
                                            <p class="text-xs text-gray-400 truncate" x-text="brand.description || brand.website_url || ''"></p>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Selected brand card --}}
                        <div x-show="selectedBrand" x-transition class="relative">
                            <div class="flex items-center gap-3 p-3 rounded-xl border-2 border-emerald-200 bg-emerald-50/40">
                                <span
                                    class="w-10 h-10 rounded-lg shrink-0 flex items-center justify-center text-white text-xs font-bold shadow-sm"
                                    :style="`background: ${selectedBrand?.primary_color || '#6B7280'}`"
                                    x-text="selectedBrand?.name?.substring(0, 2).toUpperCase()"
                                ></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900" x-text="selectedBrand?.name"></p>
                                    <p class="text-xs text-gray-500 truncate" x-text="selectedBrand?.website_url || selectedBrand?.description || ''"></p>
                                </div>
                                <button type="button" @click="clearSelection()" class="p-1 rounded-md text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors cursor-pointer" title="Change brand">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="flex items-center gap-1.5 mt-2 ml-1">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                <span class="text-xs text-emerald-600 font-medium">Brand found in our directory</span>
                            </div>
                        </div>

                        {{-- Can't find link --}}
                        <div x-show="!selectedBrand" class="pt-1">
                            <button
                                type="button"
                                @click="switchToNew()"
                                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-emerald-600 transition-colors cursor-pointer group"
                            >
                                <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-emerald-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Can't find your brand? Request to add it
                            </button>
                        </div>

                        @error('brand_id')
                            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- New brand flow: Details form --}}
                    <div x-show="flow === 'new'" class="space-y-4">
                        <x-input
                            name="brand_name"
                            label="Brand / Company Name"
                            placeholder="e.g. Awesome Tea Co"
                            required
                        />

                        <x-input
                            name="description"
                            label="Description"
                            placeholder="Brief description of your brand"
                        />

                        <x-input
                            name="website_url"
                            type="url"
                            label="Website"
                            placeholder="https://example.com"
                        />

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">Brand Logo</label>
                            <input
                                type="file"
                                name="logo"
                                accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 file:cursor-pointer"
                            >
                            @error('logo')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="p-3 rounded-lg bg-amber-50/80 border border-amber-200/60">
                            <p class="text-xs text-amber-700">
                                <span class="font-semibold">Note:</span> New brand requests are reviewed by our team. We'll get back to you within 2-3 business days.
                            </p>
                        </div>
                    </div>
                </x-card>

                {{-- Step 2: Contact & Account --}}
                <x-card class="p-6 mb-4" x-show="selectedBrand || flow === 'new'" x-transition>
                    <fieldset class="space-y-4">
                        <legend class="text-sm font-semibold text-gray-700 border-b border-gray-200 pb-2 w-full">Contact Details</legend>

                        <x-input
                            name="contact_person"
                            label="Your Full Name"
                            placeholder="e.g. Alice Wong"
                            required
                        />

                        <x-input
                            name="phone"
                            type="tel"
                            label="Phone"
                            placeholder="+60 12-345 6789"
                        />
                    </fieldset>
                </x-card>

                <x-card class="p-6 mb-6" x-show="selectedBrand || flow === 'new'" x-transition>
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
                </x-card>

                {{-- Submit --}}
                <div x-show="selectedBrand || flow === 'new'" x-transition>
                    <x-button type="submit" class="w-full justify-center">
                        <x-heroicon-o-paper-airplane class="w-4 h-4" />
                        Submit Application
                    </x-button>
                </div>
            </form>

            <p class="text-center text-sm text-gray-500 mt-5">
                Already have an account?
                <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">Sign in</a>
                &middot;
                <a href="{{ route('home') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">Back</a>
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('brandRegistration', () => ({
                flow: 'claim',
                searchQuery: '',
                brands: [],
                selectedBrand: null,
                showDropdown: false,
                loading: false,

                async searchBrands() {
                    this.loading = true;
                    try {
                        const response = await fetch(`{{ route('registration.brand.search') }}?q=${encodeURIComponent(this.searchQuery)}`);
                        this.brands = await response.json();
                        this.showDropdown = true;
                    } catch (e) {
                        this.brands = [];
                    }
                    this.loading = false;
                },

                selectBrand(brand) {
                    this.selectedBrand = brand;
                    this.showDropdown = false;
                    this.searchQuery = brand.name;
                },

                clearSelection() {
                    this.selectedBrand = null;
                    this.searchQuery = '';
                    this.$nextTick(() => this.$refs.searchInput.focus());
                },

                switchToNew() {
                    this.flow = 'new';
                    this.selectedBrand = null;
                    this.showDropdown = false;
                },

                switchToClaim() {
                    this.flow = 'claim';
                    this.$nextTick(() => this.$refs.searchInput.focus());
                }
            }));
        });
    </script>
</x-layouts.app>

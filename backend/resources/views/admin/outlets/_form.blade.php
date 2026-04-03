{{-- Shared form fields for create/edit outlet --}}
@php
    $outlet ??= null;
    $hasPlacesApi = (bool) config('services.google_maps.api_key');
@endphp

<div class="space-y-8">

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- Section 1 · Outlet Identity                                --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-x-10 gap-y-4">
        <div class="lg:pt-1">
            <div class="flex items-center gap-2.5 mb-1.5">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center ring-1 ring-blue-200/60">
                    <x-heroicon-s-building-storefront class="w-4 h-4 text-blue-600" />
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Outlet Identity</h2>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed lg:pl-[42px]">Name and active status.</p>
        </div>

        <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6 space-y-5">
            {{-- Outlet Name --}}
            <div class="space-y-1.5">
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Outlet Name <span class="text-red-500">*</span>
                </label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name', $outlet?->name) }}"
                    placeholder="e.g. Starbucks KLCC"
                    required
                    class="w-full rounded-xl border px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 @error('name') border-red-300 bg-red-50/50 @else border-gray-200 bg-gray-50/50 @enderror"
                >
                @error('name')
                    <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Active Status --}}
            <div class="space-y-1.5">
                <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                <select
                    id="is_active"
                    name="is_active"
                    class="w-full rounded-xl border px-4 py-2.5 text-sm text-gray-900 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 border-gray-200 bg-gray-50/50 cursor-pointer @error('is_active') border-red-300 bg-red-50/50 @enderror"
                >
                    <option value="1" {{ old('is_active', $outlet?->is_active ? '1' : '0') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $outlet?->is_active ? '1' : '0') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('is_active')
                    <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- Section 2 · Location                                       --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div
        class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-x-10 gap-y-4"
        x-data="outletLocationPicker()"
        x-init="$nextTick(() => initMap())"
    >
        <div class="lg:pt-1">
            <div class="flex items-center gap-2.5 mb-1.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center ring-1 ring-emerald-200/60">
                    <x-heroicon-s-map-pin class="w-4 h-4 text-emerald-600" />
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Location</h2>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed lg:pl-[42px]">Search for a place, or pin it on the map.</p>
        </div>

        <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6 space-y-5">
            {{-- Address with autocomplete dropdown --}}
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label for="address" class="block text-sm font-medium text-gray-700">
                        Address <span class="text-red-500">*</span>
                    </label>
                    @if($hasPlacesApi)
                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-600 ring-1 ring-blue-200/60">
                            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                            Google Places
                        </span>
                    @endif
                </div>
                <div class="relative" @click.outside="showSuggestions = false" @keydown.escape="showSuggestions = false">
                    <div class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                        <template x-if="!searching">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        </template>
                        <template x-if="searching">
                            <svg class="w-4 h-4 animate-spin text-emerald-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </template>
                    </div>
                    <input
                        id="address"
                        name="address"
                        type="text"
                        x-ref="addressInput"
                        x-model="address"
                        @input.debounce.350ms="searchPlaces()"
                        @focus="if (suggestions.length) showSuggestions = true"
                        @keydown.arrow-down.prevent="highlightNext()"
                        @keydown.arrow-up.prevent="highlightPrev()"
                        @keydown.enter.prevent="selectHighlighted()"
                        autocomplete="off"
                        placeholder="{{ $hasPlacesApi ? 'Type a place name or address to search...' : 'Full street address' }}"
                        required
                        class="w-full rounded-xl border pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 @error('address') border-red-300 bg-red-50/50 @else border-gray-200 bg-gray-50/50 @enderror"
                    >

                    {{-- Autocomplete dropdown --}}
                    <div
                        x-show="showSuggestions && suggestions.length > 0"
                        x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="absolute z-50 mt-1.5 w-full rounded-xl bg-white border border-gray-200 shadow-lg overflow-hidden"
                    >
                        <ul class="max-h-64 overflow-y-auto py-1">
                            <template x-for="(suggestion, idx) in suggestions" :key="suggestion.placeId">
                                <li
                                    @click="selectSuggestion(suggestion)"
                                    @mouseenter="highlightedIndex = idx"
                                    :class="highlightedIndex === idx ? 'bg-emerald-50' : 'hover:bg-gray-50'"
                                    class="flex items-start gap-3 px-3.5 py-2.5 cursor-pointer transition-colors duration-75"
                                >
                                    <div class="shrink-0 mt-0.5">
                                        <div
                                            class="w-7 h-7 rounded-lg flex items-center justify-center"
                                            :class="highlightedIndex === idx ? 'bg-emerald-100' : 'bg-gray-100'"
                                        >
                                            <svg class="w-3.5 h-3.5" :class="highlightedIndex === idx ? 'text-emerald-600' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 truncate" x-text="suggestion.mainText"></p>
                                        <p class="text-xs text-gray-500 truncate mt-0.5" x-text="suggestion.secondaryText"></p>
                                    </div>
                                </li>
                            </template>
                        </ul>
                        <div class="border-t border-gray-100 px-3 py-1.5 bg-gray-50/50">
                            <p class="text-[10px] text-gray-400 text-right">Powered by Google</p>
                        </div>
                    </div>
                </div>
                @error('address')
                    <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                @else
                    @if($hasPlacesApi)
                        <p class="text-xs text-gray-400">Start typing to see Google Places suggestions.</p>
                    @endif
                @enderror
            </div>

            {{-- Map --}}
            <div class="relative">
                <div
                    x-ref="mapContainer"
                    class="h-72 rounded-xl border border-gray-200 overflow-hidden z-0"
                    style="background: #e8ecf1;"
                ></div>

                {{-- Map empty state overlay --}}
                <div
                    x-show="!hasLocation"
                    x-transition.opacity.duration.300ms
                    class="absolute inset-0 flex flex-col items-center justify-center rounded-xl pointer-events-none"
                >
                    <div class="bg-white/90 backdrop-blur-sm rounded-lg px-4 py-3 shadow-sm border border-gray-200/60 text-center">
                        <x-heroicon-o-map-pin class="w-5 h-5 text-gray-400 mx-auto mb-1" />
                        <p class="text-xs font-medium text-gray-600">Click the map to place a pin</p>
                        @if($hasPlacesApi)
                            <p class="text-[10px] text-gray-400 mt-0.5">or search for an address above</p>
                        @endif
                    </div>
                </div>

                {{-- Location-set badge --}}
                <div
                    x-show="hasLocation"
                    x-cloak
                    x-transition.opacity.duration.300ms
                    class="absolute top-3 right-3 z-10"
                >
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-600 px-2.5 py-1 text-[11px] font-medium text-white shadow-sm">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Location set
                    </span>
                </div>
            </div>

            {{-- Latitude / Longitude --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                    <input
                        id="latitude"
                        name="latitude"
                        type="text"
                        inputmode="decimal"
                        x-model="lat"
                        @input.debounce.500ms="updateMarkerFromInputs()"
                        placeholder="e.g. 5.4164"
                        class="w-full rounded-xl border px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 @error('latitude') border-red-300 bg-red-50/50 @else border-gray-200 bg-gray-50/50 @enderror"
                    >
                    @error('latitude')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                    <input
                        id="longitude"
                        name="longitude"
                        type="text"
                        inputmode="decimal"
                        x-model="lng"
                        @input.debounce.500ms="updateMarkerFromInputs()"
                        placeholder="e.g. 100.3327"
                        class="w-full rounded-xl border px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 @error('longitude') border-red-300 bg-red-50/50 @else border-gray-200 bg-gray-50/50 @enderror"
                    >
                    @error('longitude')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <p class="text-xs text-gray-400 flex items-center gap-1.5 -mt-2">
                <x-heroicon-o-arrow-path class="w-3 h-3" />
                Coordinates sync with the map. Drag the pin, click the map, or type manually.
            </p>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- Alpine Components                                                 --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<script>
document.addEventListener('alpine:init', () => {

    /* ─── Location Picker ─── */
    Alpine.data('outletLocationPicker', () => ({
        lat: @js(old('latitude', $outlet?->latitude) ?? ''),
        lng: @js(old('longitude', $outlet?->longitude) ?? ''),
        address: @js(old('address', $outlet?->address) ?? ''),
        map: null,
        marker: null,

        // Autocomplete state
        suggestions: [],
        showSuggestions: false,
        highlightedIndex: -1,
        searching: false,
        searchAbort: null,
        hasPlacesApi: @js($hasPlacesApi),

        get hasLocation() {
            return this.lat && this.lng && !isNaN(parseFloat(this.lat)) && !isNaN(parseFloat(this.lng));
        },

        initMap() {
            const L = window.L;
            if (!L || !this.$refs.mapContainer) return;

            const defaultLat = 5.4164;
            const defaultLng = 100.3327;
            const hasCoords = this.hasLocation;
            const centerLat = hasCoords ? parseFloat(this.lat) : defaultLat;
            const centerLng = hasCoords ? parseFloat(this.lng) : defaultLng;
            const zoom = hasCoords ? 16 : 6;

            this.map = L.map(this.$refs.mapContainer, {
                scrollWheelZoom: true,
                zoomControl: true,
            }).setView([centerLat, centerLng], zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19,
            }).addTo(this.map);

            if (hasCoords) {
                this.placeMarker(centerLat, centerLng);
            }

            this.map.on('click', (e) => {
                this.placeMarker(e.latlng.lat, e.latlng.lng);
                this.lat = e.latlng.lat.toFixed(8);
                this.lng = e.latlng.lng.toFixed(8);
            });
        },

        placeMarker(lat, lng) {
            const L = window.L;
            if (this.marker) {
                this.marker.setLatLng([lat, lng]);
            } else {
                this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                this.marker.on('dragend', () => {
                    const pos = this.marker.getLatLng();
                    this.lat = pos.lat.toFixed(8);
                    this.lng = pos.lng.toFixed(8);
                });
            }
            this.map.setView([lat, lng], Math.max(this.map.getZoom(), 15));
        },

        updateMarkerFromInputs() {
            const lat = parseFloat(this.lat);
            const lng = parseFloat(this.lng);
            if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                this.placeMarker(lat, lng);
            }
        },

        async searchPlaces() {
            if (!this.hasPlacesApi || this.address.length < 2) {
                this.suggestions = [];
                this.showSuggestions = false;
                return;
            }

            // Abort previous in-flight request
            if (this.searchAbort) this.searchAbort.abort();
            this.searchAbort = new AbortController();

            this.searching = true;

            try {
                const res = await fetch(
                    @js(route('admin.places.autocomplete')) + '?q=' + encodeURIComponent(this.address),
                    { signal: this.searchAbort.signal }
                );
                const data = await res.json();

                this.suggestions = (data.suggestions || [])
                    .filter(s => s.placePrediction)
                    .map(s => ({
                        placeId: s.placePrediction.placeId,
                        mainText: s.placePrediction.structuredFormat?.mainText?.text || s.placePrediction.text?.text || '',
                        secondaryText: s.placePrediction.structuredFormat?.secondaryText?.text || '',
                        fullText: s.placePrediction.text?.text || '',
                    }));

                this.highlightedIndex = -1;
                this.showSuggestions = this.suggestions.length > 0;
            } catch (e) {
                if (e.name !== 'AbortError') {
                    console.warn('Places search failed:', e);
                }
            } finally {
                this.searching = false;
            }
        },

        async selectSuggestion(suggestion) {
            this.address = suggestion.fullText;
            this.showSuggestions = false;
            this.suggestions = [];
            this.searching = true;

            try {
                const res = await fetch(
                    @js(route('admin.places.details')) + '?place_id=' + encodeURIComponent(suggestion.placeId)
                );
                const data = await res.json();

                if (data.location) {
                    this.lat = data.location.latitude.toFixed(8);
                    this.lng = data.location.longitude.toFixed(8);
                    this.placeMarker(data.location.latitude, data.location.longitude);
                }
                if (data.formattedAddress) {
                    this.address = data.formattedAddress;
                }
            } catch (e) {
                console.warn('Place details fetch failed:', e);
            } finally {
                this.searching = false;
            }
        },

        highlightNext() {
            if (!this.showSuggestions) return;
            this.highlightedIndex = (this.highlightedIndex + 1) % this.suggestions.length;
        },

        highlightPrev() {
            if (!this.showSuggestions) return;
            this.highlightedIndex = this.highlightedIndex <= 0
                ? this.suggestions.length - 1
                : this.highlightedIndex - 1;
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.suggestions.length) {
                this.selectSuggestion(this.suggestions[this.highlightedIndex]);
            }
        },
    }));
});
</script>

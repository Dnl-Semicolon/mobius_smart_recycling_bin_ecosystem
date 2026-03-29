{{-- Shared form fields for create/edit outlet --}}
@php
    $outlet ??= null;
    $hasPlacesApi = (bool) config('services.google_maps.api_key');

    // Build full 7-day schedule for Alpine (existing data or defaults)
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    $defaultSchedule = collect($days)->mapWithKeys(fn ($d) => [
        $d => ['open' => true, 'from' => '09:00', 'to' => '22:00'],
    ])->toArray();

    $existingHours = old('operating_hours', $outlet?->operating_hours);
    $parsedHours = $existingHours ? json_decode($existingHours, true) : null;

    $scheduleData = $defaultSchedule;
    if (is_array($parsedHours)) {
        foreach ($days as $day) {
            if (isset($parsedHours[$day])) {
                $scheduleData[$day] = [
                    'open' => (bool) ($parsedHours[$day]['open'] ?? true),
                    'from' => $parsedHours[$day]['from'] ?? '09:00',
                    'to' => $parsedHours[$day]['to'] ?? '22:00',
                ];
            }
        }
    }

    // Pre-generate time slots so Alpine has them on first render
    $timeSlots = [];
    for ($h = 0; $h < 24; $h++) {
        for ($m = 0; $m < 60; $m += 30) {
            $timeSlots[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . str_pad($m, 2, '0', STR_PAD_LEFT);
        }
    }
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
            <p class="text-xs text-gray-500 leading-relaxed lg:pl-[42px]">Name, photo, and contract status.</p>
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

            {{-- Photo Upload --}}
            <div class="space-y-1.5">
                <label class="block text-sm font-medium text-gray-700">Outlet Photo</label>
                <div
                    x-data="{
                        preview: {{ $outlet?->photo_url ? "'" . e($outlet->photo_url) . "'" : 'null' }},
                        fileName: null,
                        fileSize: null,
                        dragging: false,
                        removeExisting: false,

                        handleFiles(files) {
                            if (!files || !files.length) return;
                            const file = files[0];

                            if (!file.type.startsWith('image/')) {
                                alert('Please select an image file (JPEG, PNG, GIF, WebP).');
                                return;
                            }
                            if (file.size > 2 * 1024 * 1024) {
                                alert('Image must be smaller than 2 MB.');
                                return;
                            }

                            this.fileName = file.name;
                            this.fileSize = (file.size / 1024 / 1024).toFixed(1) + ' MB';
                            this.removeExisting = false;

                            const reader = new FileReader();
                            reader.onload = (e) => { this.preview = e.target.result; };
                            reader.readAsDataURL(file);

                            this.$refs.photoInput.files = files;
                        },

                        removePhoto() {
                            this.preview = null;
                            this.fileName = null;
                            this.fileSize = null;
                            this.$refs.photoInput.value = '';
                            this.removeExisting = true;
                        }
                    }"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="dragging = false; handleFiles($event.dataTransfer.files)"
                >
                    <input type="hidden" name="remove_photo" :value="removeExisting ? '1' : '0'">
                    <input type="file" name="photo" accept="image/*" class="hidden" x-ref="photoInput"
                        @change="handleFiles($event.target.files)">

                    {{-- Upload zone --}}
                    <div
                        x-show="!preview"
                        @click="$refs.photoInput.click()"
                        :class="dragging ? 'border-emerald-400 bg-emerald-50/50 scale-[1.01]' : 'border-gray-300 bg-gray-50/30 hover:border-emerald-300 hover:bg-emerald-50/20'"
                        class="relative flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed px-6 py-10 cursor-pointer transition-all duration-200"
                    >
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center" :class="dragging && 'bg-emerald-100'">
                            <x-heroicon-o-camera class="w-6 h-6 text-gray-400" x-bind:class="dragging && '!text-emerald-500'" />
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600"><span class="font-semibold text-emerald-600">Click to upload</span> or drag and drop</p>
                            <p class="text-xs text-gray-400 mt-1">JPEG, PNG, WebP &middot; Max 2 MB</p>
                        </div>
                    </div>

                    {{-- Preview --}}
                    <div x-show="preview" x-cloak class="relative group rounded-xl overflow-hidden border border-gray-200 bg-gray-100">
                        <img :src="preview" alt="Outlet preview" class="w-full h-48 object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                        <div class="absolute bottom-0 left-0 right-0 flex items-center justify-between px-4 py-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <div class="text-white text-xs">
                                <span x-show="fileName" x-text="fileName" class="font-medium"></span>
                                <span x-show="fileSize" class="opacity-70 ml-1" x-text="fileSize"></span>
                                <span x-show="!fileName && preview" class="font-medium">Current photo</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="$refs.photoInput.click()"
                                    class="rounded-lg bg-white/20 backdrop-blur-sm px-3 py-1.5 text-xs font-medium text-white hover:bg-white/30 transition-colors cursor-pointer">
                                    Replace
                                </button>
                                <button type="button" @click="removePhoto()"
                                    class="rounded-lg bg-red-500/80 backdrop-blur-sm px-3 py-1.5 text-xs font-medium text-white hover:bg-red-600/90 transition-colors cursor-pointer">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>

                    @error('photo')
                        <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Contract Status --}}
            <x-dropdown.select
                name="contract_status"
                label="Contract Status"
                :options="collect($statuses)->mapWithKeys(fn($s) => [$s->value => $s->label()])->toArray()"
                :selected="old('contract_status', $outlet?->contract_status?->value)"
                placeholder="Select status"
            />
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

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- Section 3 · Operating Hours                                --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div
        class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-x-10 gap-y-4"
        x-data="outletHoursPicker()"
    >
        <div class="lg:pt-1">
            <div class="flex items-center gap-2.5 mb-1.5">
                <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center ring-1 ring-orange-200/60">
                    <x-heroicon-s-clock class="w-4 h-4 text-orange-600" />
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Operating Hours</h2>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed lg:pl-[42px]">Set the weekly schedule.</p>
        </div>

        <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6">
            {{-- Hidden input holds the JSON --}}
            <input type="hidden" name="operating_hours" :value="getJson()">

            {{-- Quick action bar --}}
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                <p class="text-xs text-gray-500">Toggle each day and set open/close times.</p>
                <button type="button" @click="applyMondayToAll()"
                    class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors cursor-pointer">
                    <x-heroicon-o-document-duplicate class="w-3.5 h-3.5" />
                    Copy Mon to all
                </button>
            </div>

            {{-- Day rows --}}
            <div class="space-y-1.5">
                <template x-for="(dayKey, index) in dayKeys" :key="dayKey">
                    <div
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors duration-150"
                        :class="schedule[dayKey].open ? 'bg-emerald-50/40' : 'bg-gray-50/50'"
                    >
                        {{-- Day name --}}
                        <span
                            class="w-20 text-sm font-medium shrink-0 transition-colors"
                            :class="schedule[dayKey].open ? 'text-gray-800' : 'text-gray-400'"
                            x-text="dayLabels[index]"
                        ></span>

                        {{-- Toggle switch --}}
                        <button
                            type="button"
                            @click="schedule[dayKey].open = !schedule[dayKey].open"
                            :class="schedule[dayKey].open ? 'bg-emerald-500' : 'bg-gray-300'"
                            class="relative inline-flex h-5 w-9 shrink-0 rounded-full transition-colors duration-200 cursor-pointer"
                            :aria-label="'Toggle ' + dayLabels[index]"
                        >
                            <span
                                :class="schedule[dayKey].open ? 'translate-x-4' : 'translate-x-0'"
                                class="inline-block h-5 w-5 rounded-full bg-white shadow-sm ring-1 ring-black/5 transition-transform duration-200"
                            ></span>
                        </button>

                        {{-- Time selects (shown when open) --}}
                        <div x-show="schedule[dayKey].open" x-cloak x-transition.opacity.duration.150ms class="flex items-center gap-2 flex-1 min-w-0">
                            <select
                                x-model="schedule[dayKey].from"
                                class="rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-sm text-gray-700 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-500/10 cursor-pointer"
                            >
                                @foreach ($timeSlots as $time)
                                    <option value="{{ $time }}">{{ $time }}</option>
                                @endforeach
                            </select>
                            <span class="text-gray-400 text-xs font-medium">to</span>
                            <select
                                x-model="schedule[dayKey].to"
                                class="rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-sm text-gray-700 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-500/10 cursor-pointer"
                            >
                                @foreach ($timeSlots as $time)
                                    <option value="{{ $time }}">{{ $time }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Closed label --}}
                        <div x-show="!schedule[dayKey].open" x-cloak x-transition.opacity.duration.150ms class="flex-1">
                            <span class="text-sm text-gray-400 italic">Closed</span>
                        </div>
                    </div>
                </template>
            </div>

            @error('operating_hours')
                <p class="mt-3 text-xs text-red-600 font-medium">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- Section 4 · Contact Info                                   --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-x-10 gap-y-4">
        <div class="lg:pt-1">
            <div class="flex items-center gap-2.5 mb-1.5">
                <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center ring-1 ring-violet-200/60">
                    <x-heroicon-s-user class="w-4 h-4 text-violet-600" />
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Contact Info</h2>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed lg:pl-[42px]">Person in charge at this outlet.</p>
        </div>

        <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6 space-y-5">
            {{-- Contact Name --}}
            <div class="space-y-1.5">
                <label for="contact_name" class="block text-sm font-medium text-gray-700">Contact Name</label>
                <input
                    id="contact_name"
                    name="contact_name"
                    type="text"
                    value="{{ old('contact_name', $outlet?->contact_name) }}"
                    placeholder="Person in charge"
                    class="w-full rounded-xl border px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 @error('contact_name') border-red-300 bg-red-50/50 @else border-gray-200 bg-gray-50/50 @enderror"
                >
                @error('contact_name')
                    <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone / Email --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Phone with Malaysian flag --}}
                <div class="space-y-1.5">
                    <label for="contact_phone" class="block text-sm font-medium text-gray-700">Contact Phone</label>
                    <div
                        x-data="{
                            phone: @js(old('contact_phone', $outlet?->contact_phone ?? '')),
                            valid: null,

                            format() {
                                let digits = this.phone.replace(/[^\d]/g, '');
                                if (digits.length === 0) { this.valid = null; return; }

                                // Auto-format: 01X-XXX XXXX or 01X-XXXX XXXX
                                if (digits.length >= 3 && digits.startsWith('01')) {
                                    let prefix = digits.slice(0, 3);
                                    let rest = digits.slice(3);
                                    if (rest.length <= 3) {
                                        this.phone = prefix + '-' + rest;
                                    } else if (rest.length <= 7) {
                                        this.phone = prefix + '-' + rest.slice(0, 3) + ' ' + rest.slice(3);
                                    } else {
                                        rest = rest.slice(0, 8);
                                        this.phone = prefix + '-' + rest.slice(0, 4) + ' ' + rest.slice(4);
                                    }
                                } else if (digits.length >= 2 && digits.startsWith('0')) {
                                    let prefix = digits.slice(0, 2);
                                    let rest = digits.slice(2);
                                    if (rest.length <= 4) {
                                        this.phone = prefix + '-' + rest;
                                    } else {
                                        rest = rest.slice(0, 8);
                                        this.phone = prefix + '-' + rest.slice(0, 4) + ' ' + rest.slice(4);
                                    }
                                }

                                this.valid = /^0\d[\d\s\-]{6,12}$/.test(this.phone);
                            }
                        }"
                        x-init="if (phone) format()"
                        class="relative"
                    >
                        <div class="pointer-events-none absolute left-0 top-0 bottom-0 flex items-center pl-3 gap-1.5 border-r border-gray-200 pr-2.5">
                            <img src="{{ asset('images/flag-malaysia.svg') }}" alt="Malaysia" class="w-5 h-3.5 rounded-[2px] shadow-sm ring-1 ring-black/10 object-cover">
                            <span class="text-xs text-gray-400 font-medium">+60</span>
                        </div>
                        <input
                            id="contact_phone"
                            name="contact_phone"
                            type="text"
                            x-model="phone"
                            @input="format()"
                            placeholder="012-345 6789"
                            class="w-full rounded-xl border pl-[6.5rem] pr-9 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 @error('contact_phone') border-red-300 bg-red-50/50 @else border-gray-200 bg-gray-50/50 @enderror"
                        >
                        {{-- Validity indicator --}}
                        <div class="absolute right-3 top-1/2 -translate-y-1/2">
                            <template x-if="valid === true">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            </template>
                            <template x-if="valid === false">
                                <svg class="w-4 h-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </template>
                        </div>
                    </div>
                    @error('contact_phone')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="space-y-1.5">
                    <label for="contact_email" class="block text-sm font-medium text-gray-700">Contact Email</label>
                    <input
                        id="contact_email"
                        name="contact_email"
                        type="email"
                        value="{{ old('contact_email', $outlet?->contact_email) }}"
                        placeholder="email@example.com"
                        class="w-full rounded-xl border px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 @error('contact_email') border-red-300 bg-red-50/50 @else border-gray-200 bg-gray-50/50 @enderror"
                    >
                    @error('contact_email')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- Section 5 · Notes                                          --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-x-10 gap-y-4">
        <div class="lg:pt-1">
            <div class="flex items-center gap-2.5 mb-1.5">
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center ring-1 ring-amber-200/60">
                    <x-heroicon-s-document-text class="w-4 h-4 text-amber-600" />
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Notes</h2>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed lg:pl-[42px]">Optional internal notes or reminders.</p>
        </div>

        <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6">
            <textarea
                name="notes"
                id="notes"
                rows="4"
                placeholder="Additional notes about this outlet..."
                class="w-full rounded-xl border px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 resize-none @error('notes') border-red-300 bg-red-50/50 @else border-gray-200 bg-gray-50/50 @enderror"
            >{{ old('notes', $outlet?->notes) }}</textarea>
            @error('notes')
                <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
            @enderror
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

    /* ─── Operating Hours Picker ─── */
    Alpine.data('outletHoursPicker', () => ({
        dayKeys: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
        dayLabels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
        timeSlots: @js($timeSlots),
        schedule: @js($scheduleData),

        getJson() {
            return JSON.stringify(this.schedule);
        },

        applyMondayToAll() {
            const mon = this.schedule.monday;
            this.dayKeys.forEach(day => {
                if (day !== 'monday') {
                    this.schedule[day] = { open: mon.open, from: mon.from, to: mon.to };
                }
            });
        },
    }));
});
</script>

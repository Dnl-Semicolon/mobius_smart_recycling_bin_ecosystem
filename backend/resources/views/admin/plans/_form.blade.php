{{-- Shared form fields for create/edit plan --}}
@php
    $plan ??= null;
@endphp

<div class="space-y-8">

    {{-- Section 1: Plan Identity --}}
    <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-x-10 gap-y-4">
        <div class="lg:pt-1">
            <div class="flex items-center gap-2.5 mb-1.5">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center ring-1 ring-blue-200/60">
                    <x-heroicon-s-rectangle-group class="w-4 h-4 text-blue-600" />
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Plan Identity</h2>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed lg:pl-[42px]">Name, description, and status.</p>
        </div>

        <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6 space-y-5">
            {{-- Plan Name --}}
            <div class="space-y-1.5">
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Plan Name <span class="text-red-500">*</span>
                </label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name', $plan?->name) }}"
                    placeholder="e.g. Starter, Business, Enterprise"
                    required
                    class="w-full rounded-xl border px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 @error('name') border-red-300 bg-red-50/50 @else border-gray-200 bg-gray-50/50 @enderror"
                >
                @error('name')
                    <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div class="space-y-1.5">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="Brief description of what this plan includes..."
                    class="w-full rounded-xl border px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 @error('description') border-red-300 bg-red-50/50 @else border-gray-200 bg-gray-50/50 @enderror"
                >{{ old('description', $plan?->description) }}</textarea>
                @error('description')
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
                    <option value="1" {{ old('is_active', $plan?->is_active ? '1' : '0') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $plan?->is_active ? '1' : '0') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('is_active')
                    <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Section 2: Pricing --}}
    <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-x-10 gap-y-4">
        <div class="lg:pt-1">
            <div class="flex items-center gap-2.5 mb-1.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center ring-1 ring-emerald-200/60">
                    <x-heroicon-s-banknotes class="w-4 h-4 text-emerald-600" />
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Pricing</h2>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed lg:pl-[42px]">Monthly and yearly pricing in MYR.</p>
        </div>

        <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6 space-y-5">
            <div class="grid grid-cols-2 gap-4">
                {{-- Monthly Price --}}
                <div class="space-y-1.5">
                    <label for="price_monthly" class="block text-sm font-medium text-gray-700">
                        Monthly Price (RM) <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="price_monthly"
                        name="price_monthly"
                        type="number"
                        step="0.01"
                        min="0"
                        value="{{ old('price_monthly', $plan?->price_monthly) }}"
                        placeholder="e.g. 99.00"
                        required
                        class="w-full rounded-xl border px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 @error('price_monthly') border-red-300 bg-red-50/50 @else border-gray-200 bg-gray-50/50 @enderror"
                    >
                    @error('price_monthly')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Yearly Price --}}
                <div class="space-y-1.5">
                    <label for="price_yearly" class="block text-sm font-medium text-gray-700">
                        Yearly Price (RM) <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="price_yearly"
                        name="price_yearly"
                        type="number"
                        step="0.01"
                        min="0"
                        value="{{ old('price_yearly', $plan?->price_yearly) }}"
                        placeholder="e.g. 990.00"
                        required
                        class="w-full rounded-xl border px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 @error('price_yearly') border-red-300 bg-red-50/50 @else border-gray-200 bg-gray-50/50 @enderror"
                    >
                    @error('price_yearly')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Section 3: Features --}}
    <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-x-10 gap-y-4">
        <div class="lg:pt-1">
            <div class="flex items-center gap-2.5 mb-1.5">
                <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center ring-1 ring-violet-200/60">
                    <x-heroicon-s-list-bullet class="w-4 h-4 text-violet-600" />
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Features</h2>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed lg:pl-[42px]">One feature per line.</p>
        </div>

        <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6 space-y-5">
            <div class="space-y-1.5">
                <label for="features" class="block text-sm font-medium text-gray-700">Feature List</label>
                <textarea
                    id="features"
                    name="features"
                    rows="6"
                    placeholder="Up to 5 bins&#10;Basic analytics&#10;Email support"
                    class="w-full rounded-xl border px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 @error('features') border-red-300 bg-red-50/50 @else border-gray-200 bg-gray-50/50 @enderror"
                >{{ old('features', is_array($plan?->features) ? implode("\n", $plan->features) : $plan?->features) }}</textarea>
                @error('features')
                    <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-400">Enter one feature per line. These will be displayed as a checklist on the plan card.</p>
            </div>
        </div>
    </div>

</div>

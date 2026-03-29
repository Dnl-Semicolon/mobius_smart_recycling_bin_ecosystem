{{-- Shared form fields for create/edit reward --}}
@php $reward ??= null; @endphp

<div class="space-y-8">

    {{-- Reward Details --}}
    <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-x-10 gap-y-4">
        <div class="lg:pt-1">
            <div class="flex items-center gap-2.5 mb-1.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center ring-1 ring-emerald-200/60">
                    <x-heroicon-s-gift class="w-4 h-4 text-emerald-600" />
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Reward Details</h2>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed lg:pl-[42px]">Name, description, and pricing for this reward.</p>
        </div>

        <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6 space-y-5">
            {{-- Name --}}
            <div class="space-y-1.5">
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Reward Name <span class="text-red-500">*</span>
                </label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name', $reward?->name) }}"
                    placeholder="e.g. Free Americano"
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
                    name="description"
                    id="description"
                    rows="3"
                    placeholder="Short description of the reward"
                    class="w-full rounded-xl border px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 resize-none @error('description') border-red-300 bg-red-50/50 @else border-gray-200 bg-gray-50/50 @enderror"
                >{{ old('description', $reward?->description) }}</textarea>
                @error('description')
                    <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Points Cost --}}
            <div class="space-y-1.5">
                <label for="points_cost" class="block text-sm font-medium text-gray-700">
                    Points Cost <span class="text-red-500">*</span>
                </label>
                <input
                    id="points_cost"
                    name="points_cost"
                    type="number"
                    min="1"
                    value="{{ old('points_cost', $reward?->points_cost) }}"
                    placeholder="100"
                    required
                    class="w-full rounded-xl border px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 @error('points_cost') border-red-300 bg-red-50/50 @else border-gray-200 bg-gray-50/50 @enderror"
                >
                @error('points_cost')
                    <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                @else
                    <p class="text-xs text-gray-400">How many points a customer spends to redeem this reward.</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Availability --}}
    <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-x-10 gap-y-4">
        <div class="lg:pt-1">
            <div class="flex items-center gap-2.5 mb-1.5">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center ring-1 ring-blue-200/60">
                    <x-heroicon-s-clock class="w-4 h-4 text-blue-600" />
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Availability</h2>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed lg:pl-[42px]">Stock limits, expiry, and visibility.</p>
        </div>

        <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6 space-y-5">
            {{-- Stock / Expires At --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                    <input
                        id="stock"
                        name="stock"
                        type="number"
                        min="0"
                        value="{{ old('stock', $reward?->stock) }}"
                        placeholder="Unlimited"
                        class="w-full rounded-xl border px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 @error('stock') border-red-300 bg-red-50/50 @else border-gray-200 bg-gray-50/50 @enderror"
                    >
                    @error('stock')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @else
                        <p class="text-xs text-gray-400">Leave empty for unlimited</p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="expires_at" class="block text-sm font-medium text-gray-700">Expires At</label>
                    <input
                        id="expires_at"
                        name="expires_at"
                        type="datetime-local"
                        value="{{ old('expires_at', $reward?->expires_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full rounded-xl border px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10 @error('expires_at') border-red-300 bg-red-50/50 @else border-gray-200 bg-gray-50/50 @enderror"
                    >
                    @error('expires_at')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @else
                        <p class="text-xs text-gray-400">Optional expiry date</p>
                    @enderror
                </div>
            </div>

            {{-- Active toggle --}}
            <div class="pt-1">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="active" value="0">
                    <input
                        type="checkbox"
                        name="active"
                        value="1"
                        @checked(old('active', $reward?->active ?? true))
                        class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-200 cursor-pointer"
                    >
                    <div>
                        <span class="text-sm font-medium text-gray-700">Active</span>
                        <p class="text-xs text-gray-400">When active, this reward is visible to customers in the app.</p>
                    </div>
                </label>
            </div>
        </div>
    </div>

</div>

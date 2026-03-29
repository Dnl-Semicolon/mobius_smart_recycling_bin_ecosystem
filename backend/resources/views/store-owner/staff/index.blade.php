<x-layouts.store-owner title="Staff Management">
    <x-slot:header>
        Staff Management
    </x-slot:header>

    @php $brandColor = $brand->primary_color ?? '#10b981'; @endphp

    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-lg font-bold text-gray-900">Staff Management</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ $brand->name }} — manage branch managers across your outlets</p>
    </div>

    {{-- Invite form --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
        <div class="flex items-center gap-2.5 mb-4">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 bg-emerald-50">
                <x-heroicon-o-user-plus class="w-4 h-4 text-emerald-600" />
            </div>
            <h3 class="text-sm font-semibold text-gray-900">Invite Branch Manager</h3>
        </div>

        <form action="{{ route('store.staff.invite') }}" method="POST">
            @csrf
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <label for="email" class="block text-xs font-medium text-gray-600 mb-1">Email address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="manager@example.com"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                    >
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:w-56">
                    <label for="outlet_id" class="block text-xs font-medium text-gray-600 mb-1">Outlet</label>
                    <select
                        id="outlet_id"
                        name="outlet_id"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                    >
                        @foreach ($outlets as $outlet)
                            <option value="{{ $outlet->id }}" @selected(old('outlet_id') == $outlet->id)>{{ $outlet->name }}</option>
                        @endforeach
                    </select>
                    @error('outlet_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:self-end">
                    <button
                        type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 transition-colors cursor-pointer"
                    >
                        <x-heroicon-o-paper-airplane class="w-4 h-4" />
                        Invite Manager
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Staff by outlet --}}
    @if ($outlets->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center">
            <x-heroicon-o-building-storefront class="w-10 h-10 text-gray-300 mx-auto mb-3" />
            <p class="text-sm text-gray-400">No outlets to manage</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($staffByOutlet as $group)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/50">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-building-storefront class="w-4 h-4 text-gray-400" />
                            <h3 class="text-sm font-semibold text-gray-900">{{ $group['outlet']->name }}</h3>
                            <span class="text-xs text-gray-400">({{ $group['managers']->count() }} {{ Str::plural('manager', $group['managers']->count()) }})</span>
                        </div>
                    </div>

                    @if ($group['managers']->isEmpty())
                        <div class="px-5 py-6 text-center">
                            <p class="text-sm text-gray-400">No managers assigned</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($group['managers'] as $manager)
                                <div class="flex items-center gap-3 px-5 py-3">
                                    <span class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-xs font-semibold text-emerald-700 shrink-0">
                                        {{ strtoupper(substr($manager->name, 0, 1)) }}
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $manager->name }}</p>
                                        <p class="text-xs text-gray-400 truncate">{{ $manager->email }}</p>
                                    </div>
                                    <form action="{{ route('store.staff.remove', $manager) }}" method="POST" class="shrink-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors cursor-pointer" onclick="return confirm('Remove {{ $manager->name }} from this outlet?')">
                                            <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.store-owner>

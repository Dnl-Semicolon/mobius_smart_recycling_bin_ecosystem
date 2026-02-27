<x-layouts.admin :title="$outlet->name">
    <x-slot:back>
        <x-back-button href="{{ route('admin.outlets.index') }}" />
    </x-slot:back>

    <x-slot:header>
        {{ $outlet->name }}
    </x-slot:header>

    <x-slot:actions>
        <x-button href="{{ route('admin.outlets.edit', $outlet) }}">
            <x-heroicon-o-pencil-square class="w-4 h-4" />
            Edit
        </x-button>
    </x-slot:actions>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 text-emerald-700 text-sm flex items-center gap-2">
            <x-heroicon-o-check-circle class="w-5 h-5 shrink-0" />
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-6">
        {{-- Status Badge --}}
        <div class="flex items-center gap-2">
            @php
                $statusColors = [
                    'active' => 'bg-emerald-100 text-emerald-700',
                    'inactive' => 'bg-gray-100 text-gray-600',
                    'pending' => 'bg-yellow-100 text-yellow-700',
                ];
            @endphp
            <span class="text-sm font-medium rounded-full px-3 py-1 {{ $statusColors[$outlet->contract_status->value] ?? 'bg-gray-100 text-gray-600' }}">
                {{ ucfirst($outlet->contract_status->value) }}
            </span>
            <span class="text-sm text-gray-400 flex items-center gap-1">
                <x-heroicon-o-archive-box class="w-4 h-4" />
                {{ $outlet->current_bins_count }} {{ Str::plural('bin', $outlet->current_bins_count) }} assigned
            </span>
        </div>

        {{-- Outlet Details --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-information-circle class="w-3.5 h-3.5" />
                    Outlet Info
                </h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-gray-400">Name</dt>
                        <dd class="text-gray-900">{{ $outlet->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Address</dt>
                        <dd class="text-gray-900">{{ $outlet->address }}</dd>
                    </div>
                    @if ($outlet->latitude && $outlet->longitude)
                        <div>
                            <dt class="text-xs text-gray-400">Coordinates</dt>
                            <dd class="text-gray-900 flex items-center gap-2">
                                <span>{{ $outlet->latitude }}, {{ $outlet->longitude }}</span>
                                <a
                                    href="https://www.google.com/maps?q={{ $outlet->latitude }},{{ $outlet->longitude }}"
                                    target="_blank"
                                    class="text-emerald-600 hover:text-emerald-700 text-sm inline-flex items-center gap-1"
                                >
                                    <x-heroicon-o-map-pin class="w-3.5 h-3.5" />
                                    Map
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if ($outlet->operating_hours)
                        <div>
                            <dt class="text-xs text-gray-400">Operating Hours</dt>
                            <dd class="text-gray-900 flex items-center gap-1.5">
                                <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                                {{ $outlet->operating_hours }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </x-card>

        {{-- Contact Info --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-user class="w-3.5 h-3.5" />
                    Contact Info
                </h2>
                @if ($outlet->contact_name || $outlet->contact_phone || $outlet->contact_email)
                    <dl class="space-y-3">
                        @if ($outlet->contact_name)
                            <div>
                                <dt class="text-xs text-gray-400">Contact Name</dt>
                                <dd class="text-gray-900">{{ $outlet->contact_name }}</dd>
                            </div>
                        @endif
                        @if ($outlet->contact_phone)
                            <div>
                                <dt class="text-xs text-gray-400">Phone</dt>
                                <dd class="text-gray-900 flex items-center gap-1.5">
                                    <x-heroicon-o-phone class="w-4 h-4 text-gray-400" />
                                    {{ $outlet->contact_phone }}
                                </dd>
                            </div>
                        @endif
                        @if ($outlet->contact_email)
                            <div>
                                <dt class="text-xs text-gray-400">Email</dt>
                                <dd class="text-gray-900">
                                    <a href="mailto:{{ $outlet->contact_email }}" class="text-emerald-600 hover:text-emerald-700 inline-flex items-center gap-1.5">
                                        <x-heroicon-o-envelope class="w-4 h-4" />
                                        {{ $outlet->contact_email }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                    </dl>
                @else
                    <p class="text-gray-400 text-sm">No contact info on file</p>
                @endif
            </div>
        </x-card>

        {{-- Notes --}}
        @if ($outlet->notes)
            <x-card>
                <div class="form-section">
                    <h2 class="form-section-title flex items-center gap-1.5">
                        <x-heroicon-o-document-text class="w-3.5 h-3.5" />
                        Notes
                    </h2>
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $outlet->notes }}</p>
                </div>
            </x-card>
        @endif

        {{-- Assigned Bins --}}
        <x-card>
            <div class="form-section">
                <h2 class="form-section-title flex items-center gap-1.5">
                    <x-heroicon-o-archive-box class="w-3.5 h-3.5" />
                    Assigned Bins
                </h2>
                @if ($outlet->bins->isEmpty())
                    <p class="text-gray-400 text-sm">No bins assigned to this outlet</p>
                @else
                    <div class="space-y-3 mt-4">
                        @foreach ($outlet->bins as $bin)
                            <a
                                href="{{ route('admin.bins.show', $bin) }}"
                                class="block p-4 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                                            <x-heroicon-s-archive-box class="w-4 h-4 text-emerald-600" />
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $bin->serial_number }}</p>
                                            @php
                                                $binStatusColors = [
                                                    'active' => 'text-emerald-600',
                                                    'inactive' => 'text-gray-500',
                                                    'maintenance' => 'text-orange-600',
                                                ];
                                            @endphp
                                            <p class="text-xs {{ $binStatusColors[$bin->status->value] ?? 'text-gray-500' }}">
                                                {{ ucfirst($bin->status->value) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @php
                                            $fillColor = match(true) {
                                                $bin->fill_level >= 80 => 'bg-red-500',
                                                $bin->fill_level >= 50 => 'bg-yellow-500',
                                                default => 'bg-emerald-500',
                                            };
                                        @endphp
                                        <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full {{ $fillColor }} rounded-full" style="width: {{ $bin->fill_level }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium {{ $bin->fill_level >= 80 ? 'text-red-600' : 'text-gray-600' }}">
                                            {{ $bin->fill_level }}%
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-card>

        {{-- Delete Action --}}
        <div class="flex justify-end">
            <form
                action="{{ route('admin.outlets.destroy', $outlet) }}"
                method="POST"
                hx-boost="false"
                onsubmit="return confirm('Delete this outlet? This action can be undone (soft delete).')"
            >
                @csrf
                @method('DELETE')

                <x-button type="submit" variant="danger">
                    <x-heroicon-o-trash class="w-4 h-4" />
                    Delete
                </x-button>
            </form>
        </div>
    </div>
</x-layouts.admin>

@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Select an option',
    'searchable' => false,
    'searchPlaceholder' => 'Search...',
    'hint' => null,
    'required' => false,
    'header' => null,
])

@php
    $dropdownId = 'dropdown-' . $name . '-' . uniqid();
    $hasError = $errors->has($name);
    $selectedValue = old($name, $selected);

    // Resolve the display label for the currently selected value
    $selectedLabel = null;
    foreach ($options as $optValue => $optLabel) {
        $actualValue = is_numeric($optValue) ? $optLabel : $optValue;
        if ((string) $selectedValue === (string) $actualValue) {
            $selectedLabel = $optLabel;
            break;
        }
    }
@endphp

<div class="space-y-1.5" data-dropdown-select id="{{ $dropdownId }}">
    @if ($label)
        <label class="block text-sm font-medium text-gray-600">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    {{-- Hidden input to hold the actual form value --}}
    <input type="hidden" name="{{ $name }}" value="{{ $selectedValue }}" data-dropdown-value>

    <div class="relative">
        {{-- Trigger button --}}
        <button
            type="button"
            data-dropdown-trigger
            aria-haspopup="listbox"
            aria-expanded="false"
            class="flex w-full items-center justify-between rounded-xl border px-4 py-2.5 text-sm text-left transition-colors duration-150
                cursor-pointer
                {{ $hasError
                    ? 'border-red-300 bg-red-50/50 text-red-900 focus:border-red-400 focus:ring-2 focus:ring-red-500/10'
                    : 'border-gray-200/80 bg-white/60 text-gray-900 hover:bg-white hover:border-gray-300 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10' }}"
        >
            <span data-dropdown-label class="{{ $selectedLabel ? 'text-gray-900' : 'text-gray-400' }}">
                {{ $selectedLabel ?? $placeholder }}
            </span>
            <x-heroicon-o-chevron-down class="ml-2 h-4 w-4 shrink-0 text-gray-400 transition-transform duration-150" data-dropdown-chevron />
        </button>

        {{-- Dropdown panel --}}
        <div
            data-dropdown-panel
            role="listbox"
            aria-label="{{ $label ?? $placeholder }}"
            class="absolute z-50 mt-1.5 w-full origin-top rounded-lg border border-gray-200 bg-white shadow-lg shadow-black/8 opacity-0 pointer-events-none translate-y-[-4px] transition-all duration-150"
        >
            {{-- Header --}}
            @if ($header || $searchable)
                <div class="border-b border-gray-200">
                    @if ($header)
                        <div class="px-4 py-2.5 text-xs font-semibold text-gray-900">
                            {{ $header }}
                        </div>
                    @endif

                    @if ($searchable)
                        <div class="px-3 {{ $header ? '' : 'pt-2.5' }} pb-2.5">
                            <div class="relative">
                                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                <input
                                    type="text"
                                    data-dropdown-search
                                    placeholder="{{ $searchPlaceholder }}"
                                    autocomplete="off"
                                    class="w-full rounded-md border border-gray-200 bg-gray-50 py-1.5 pl-9 pr-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-1 focus:ring-emerald-500/20 focus:outline-none"
                                >
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Options list --}}
            <div data-dropdown-options class="max-h-64 overflow-y-auto overscroll-contain py-1">
                @foreach ($options as $optValue => $optLabel)
                    @php
                        $actualValue = is_numeric($optValue) ? $optLabel : $optValue;
                        $isSelected = (string) $selectedValue === (string) $actualValue;
                    @endphp
                    <button
                        type="button"
                        role="option"
                        aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                        data-dropdown-option
                        data-value="{{ $actualValue }}"
                        data-label="{{ $optLabel }}"
                        class="flex w-full items-center gap-3 px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-50 focus:bg-gray-50 focus:outline-none transition-colors duration-100 cursor-pointer {{ $isSelected ? 'font-medium text-gray-900' : '' }}"
                    >
                        <span class="flex h-4 w-4 shrink-0 items-center justify-center">
                            @if ($isSelected)
                                <x-heroicon-s-check class="h-4 w-4 text-gray-900" />
                            @endif
                        </span>
                        <span data-option-text class="truncate">{{ $optLabel }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Empty state for search --}}
            @if ($searchable)
                <div data-dropdown-empty class="hidden px-4 py-6 text-center text-sm text-gray-400">
                    No results found.
                </div>
            @endif
        </div>
    </div>

    @if ($hasError)
        <p class="text-xs text-red-600 font-medium">{{ $errors->first($name) }}</p>
    @elseif ($hint)
        <p class="text-xs text-gray-400">{{ $hint }}</p>
    @endif
</div>

@once
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-dropdown-select]').forEach(initDropdownSelect);

    // Re-init on Livewire/Turbo navigations if needed
    function initDropdownSelect(container) {
        var trigger = container.querySelector('[data-dropdown-trigger]');
        var panel = container.querySelector('[data-dropdown-panel]');
        var hiddenInput = container.querySelector('[data-dropdown-value]');
        var labelEl = container.querySelector('[data-dropdown-label]');
        var chevron = container.querySelector('[data-dropdown-chevron]');
        var searchInput = container.querySelector('[data-dropdown-search]');
        var optionsContainer = container.querySelector('[data-dropdown-options]');
        var emptyEl = container.querySelector('[data-dropdown-empty]');
        var options = Array.from(container.querySelectorAll('[data-dropdown-option]'));
        var isOpen = false;
        var focusedIndex = -1;

        function open() {
            if (isOpen) return;
            isOpen = true;

            // Position: check if there's room below
            var triggerRect = trigger.getBoundingClientRect();
            var spaceBelow = window.innerHeight - triggerRect.bottom;
            var spaceAbove = triggerRect.top;
            var panelHeight = Math.min(400, panel.scrollHeight || 300);

            if (spaceBelow < panelHeight && spaceAbove > spaceBelow) {
                panel.style.bottom = '100%';
                panel.style.top = 'auto';
                panel.style.marginBottom = '6px';
                panel.style.marginTop = '0';
                panel.style.transformOrigin = 'bottom';
            } else {
                panel.style.top = '100%';
                panel.style.bottom = 'auto';
                panel.style.marginTop = '6px';
                panel.style.marginBottom = '0';
                panel.style.transformOrigin = 'top';
            }

            panel.style.opacity = '1';
            panel.style.pointerEvents = 'auto';
            panel.style.transform = 'translateY(0)';
            trigger.setAttribute('aria-expanded', 'true');
            chevron.style.transform = 'rotate(180deg)';

            if (searchInput) {
                searchInput.value = '';
                filterOptions('');
                setTimeout(function () { searchInput.focus(); }, 10);
            }

            // Scroll selected into view
            var selectedOpt = optionsContainer.querySelector('[aria-selected="true"]');
            if (selectedOpt) {
                focusedIndex = options.indexOf(selectedOpt);
                selectedOpt.scrollIntoView({ block: 'nearest' });
            }
        }

        function close() {
            if (!isOpen) return;
            isOpen = false;
            panel.style.opacity = '0';
            panel.style.pointerEvents = 'none';
            panel.style.transform = 'translateY(-4px)';
            trigger.setAttribute('aria-expanded', 'false');
            chevron.style.transform = '';
            focusedIndex = -1;
            clearFocus();
        }

        function toggle() {
            isOpen ? close() : open();
        }

        function selectOption(optionEl) {
            var value = optionEl.getAttribute('data-value');
            var label = optionEl.getAttribute('data-label');

            hiddenInput.value = value;
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            labelEl.textContent = label;
            labelEl.classList.remove('text-gray-400');
            labelEl.classList.add('text-gray-900');

            // Update checkmarks
            options.forEach(function (opt) {
                var check = opt.querySelector('span:first-child');
                if (opt === optionEl) {
                    opt.setAttribute('aria-selected', 'true');
                    opt.classList.add('font-medium', 'text-gray-900');
                    check.innerHTML = '<svg class="h-4 w-4 text-gray-900" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd" /></svg>';
                } else {
                    opt.setAttribute('aria-selected', 'false');
                    opt.classList.remove('font-medium', 'text-gray-900');
                    check.innerHTML = '';
                }
            });

            // Trigger change event on hidden input for any listeners
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            close();
            trigger.focus();
        }

        function getVisibleOptions() {
            return options.filter(function (opt) {
                return opt.style.display !== 'none';
            });
        }

        function clearFocus() {
            options.forEach(function (opt) {
                opt.classList.remove('bg-gray-50');
            });
        }

        function setFocusedOption(index) {
            var visible = getVisibleOptions();
            if (visible.length === 0) return;
            clearFocus();
            focusedIndex = Math.max(0, Math.min(index, visible.length - 1));
            var opt = visible[focusedIndex];
            opt.classList.add('bg-gray-50');
            opt.scrollIntoView({ block: 'nearest' });
        }

        function filterOptions(query) {
            if (!searchInput) return;
            var q = query.toLowerCase().trim();
            var visibleCount = 0;

            options.forEach(function (opt) {
                var text = opt.getAttribute('data-label').toLowerCase();
                if (!q || text.indexOf(q) !== -1) {
                    opt.style.display = '';
                    visibleCount++;
                } else {
                    opt.style.display = 'none';
                }
            });

            if (emptyEl) {
                emptyEl.classList.toggle('hidden', visibleCount > 0);
            }
            optionsContainer.classList.toggle('hidden', visibleCount === 0);
            focusedIndex = -1;
        }

        // Event listeners
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            toggle();
        });

        options.forEach(function (opt) {
            opt.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                selectOption(opt);
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                filterOptions(this.value);
            });

            searchInput.addEventListener('keydown', function (e) {
                handleKeyboard(e);
            });
        }

        trigger.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter' || e.key === ' ') {
                if (!isOpen) {
                    e.preventDefault();
                    open();
                }
            }
        });

        function handleKeyboard(e) {
            if (!isOpen) return;
            var visible = getVisibleOptions();

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    setFocusedOption(focusedIndex + 1);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    setFocusedOption(focusedIndex - 1);
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (focusedIndex >= 0 && focusedIndex < visible.length) {
                        selectOption(visible[focusedIndex]);
                    }
                    break;
                case 'Escape':
                    e.preventDefault();
                    close();
                    trigger.focus();
                    break;
                case 'Tab':
                    close();
                    break;
            }
        }

        container.addEventListener('keydown', function (e) {
            if (isOpen && e.key === 'Escape') {
                e.preventDefault();
                close();
                trigger.focus();
            }
            if (isOpen && !searchInput) {
                handleKeyboard(e);
            }
        });

        // Close on click outside
        document.addEventListener('click', function (e) {
            if (isOpen && !container.contains(e.target)) {
                close();
            }
        });
    }
});
</script>
@endonce

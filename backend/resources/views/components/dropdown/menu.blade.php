@props([
    'align' => 'right',
    'width' => '240px',
])

@php
    $menuId = 'dropdown-menu-' . uniqid();
    $alignClass = $align === 'left' ? 'left-0' : 'right-0';
@endphp

<div class="relative inline-flex" data-dropdown-menu id="{{ $menuId }}">
    {{-- Trigger --}}
    <button
        type="button"
        data-dropdown-menu-trigger
        aria-haspopup="true"
        aria-expanded="false"
        class="inline-flex items-center transition-opacity duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/20 focus-visible:ring-offset-2 rounded-[inherit] cursor-pointer"
    >
        {{ $trigger }}
    </button>

    {{-- Panel --}}
    <div
        data-dropdown-menu-panel
        role="menu"
        class="absolute {{ $alignClass }} z-50 mt-1 rounded-lg border border-gray-300 bg-white shadow-lg shadow-black/12 opacity-0 pointer-events-none translate-y-[-4px] transition-all duration-150 py-2"
        style="width: {{ $width }}; top: 100%;"
    >
        {{ $slot }}
    </div>
</div>

@once
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-dropdown-menu]').forEach(function (container) {
        var trigger = container.querySelector('[data-dropdown-menu-trigger]');
        var panel = container.querySelector('[data-dropdown-menu-panel]');
        var isOpen = false;

        function open() {
            if (isOpen) return;
            isOpen = true;

            // Position: check space below vs above
            var triggerRect = trigger.getBoundingClientRect();
            var spaceBelow = window.innerHeight - triggerRect.bottom;
            var spaceAbove = triggerRect.top;
            var panelHeight = Math.min(500, panel.scrollHeight || 300);

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

            // Focus first focusable item
            var firstItem = panel.querySelector('[role="menuitem"]');
            if (firstItem) {
                setTimeout(function () { firstItem.focus(); }, 10);
            }
        }

        function close() {
            if (!isOpen) return;
            isOpen = false;
            panel.style.opacity = '0';
            panel.style.pointerEvents = 'none';
            panel.style.transform = 'translateY(-4px)';
            trigger.setAttribute('aria-expanded', 'false');
        }

        function toggle() {
            isOpen ? close() : open();
        }

        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            toggle();
        });

        // Keyboard navigation within menu
        panel.addEventListener('keydown', function (e) {
            var items = Array.from(panel.querySelectorAll('[role="menuitem"]'));
            var currentIndex = items.indexOf(document.activeElement);

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    var next = currentIndex + 1;
                    if (next >= items.length) next = 0;
                    items[next].focus();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    var prev = currentIndex - 1;
                    if (prev < 0) prev = items.length - 1;
                    items[prev].focus();
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
        });

        // Close on click outside
        document.addEventListener('click', function (e) {
            if (isOpen && !container.contains(e.target)) {
                close();
            }
        });

        // Close on Escape from trigger
        trigger.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen) {
                e.preventDefault();
                close();
            }
            if ((e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') && !isOpen) {
                e.preventDefault();
                open();
            }
        });
    });
});
</script>
@endonce

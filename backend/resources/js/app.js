import './bootstrap';
import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';
import L from 'leaflet';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// Fix Leaflet default marker icon paths for Vite bundler
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconUrl: markerIcon,
    iconRetinaUrl: markerIcon2x,
    shadowUrl: markerShadow,
});
window.L = L;

Alpine.plugin(persist);
window.Alpine = Alpine;
Alpine.start();

window.confirmChanges = function confirmChanges(form, original, labels, backUrl) {
    const formData = new FormData(form);
    const changes = [];

    for (const [key, value] of formData.entries()) {
        if (key === '_token' || key === '_method') {
            continue;
        }

        const originalValue = original[key] ?? '';
        const currentValue = value ?? '';

        if (originalValue !== currentValue) {
            const label = labels[key] || key;
            const fromValue = originalValue || '(empty)';
            const toValue = currentValue || '(empty)';

            changes.push(`${label}: "${fromValue}" \u2192 "${toValue}"`);
        }
    }

    if (changes.length === 0) {
        if (backUrl) {
            window.location.href = backUrl;
        }

        return false;
    }

    return confirm(`Save these changes?\n\n${changes.join('\n')}`);
};

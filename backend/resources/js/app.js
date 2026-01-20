import './bootstrap';

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

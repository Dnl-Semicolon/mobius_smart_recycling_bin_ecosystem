export function normalizeEmail(value: string): string {
    return value.trim().toLowerCase();
}

export function isValidEmail(value: string): boolean {
    const normalized = normalizeEmail(value);

    if (normalized === '') {
        return false;
    }

    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalized);
}

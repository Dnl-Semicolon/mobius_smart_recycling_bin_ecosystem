export const MALAYSIAN_PHONE_PLACEHOLDER = '012-345 6789';

function digitsOnly(value: string): string {
    return value.replace(/\D/g, '');
}

function toLocalDigits(value: string): string {
    const trimmed = value.trim();

    if (trimmed === '') {
        return '';
    }

    const digits = digitsOnly(trimmed);

    if (digits.startsWith('60')) {
        return `0${digits.slice(2)}`;
    }

    return digits;
}

function isValidLocalMobile(value: string): boolean {
    const digits = digitsOnly(value);

    if (!digits.startsWith('01')) {
        return false;
    }

    if (digits.startsWith('011')) {
        return /^011\d{8}$/.test(digits);
    }

    return /^01\d{8}$/.test(digits);
}

export function normalizePhone(value: string): string {
    const localDigits = toLocalDigits(value);

    if (!isValidLocalMobile(localDigits)) {
        return '';
    }

    return `+60${localDigits.slice(1)}`;
}

export function isValidMalaysianMobile(value: string): boolean {
    return normalizePhone(value) !== '';
}

export function formatPhoneForInput(value: string): string {
    const localDigits = toLocalDigits(value);

    if (localDigits === '') {
        return '';
    }

    if (localDigits.startsWith('011')) {
        const digits = localDigits.slice(0, 11);

        if (digits.length <= 3) {
            return digits;
        }

        if (digits.length <= 7) {
            return `${digits.slice(0, 3)}-${digits.slice(3)}`;
        }

        return `${digits.slice(0, 3)}-${digits.slice(3, 7)} ${digits.slice(7)}`;
    }

    const digits = localDigits.slice(0, 10);

    if (digits.length <= 3) {
        return digits;
    }

    if (digits.length <= 6) {
        return `${digits.slice(0, 3)}-${digits.slice(3)}`;
    }

    return `${digits.slice(0, 3)}-${digits.slice(3, 6)} ${digits.slice(6)}`;
}

export function formatPhoneForDisplay(value: string | null): string {
    if (value === null || value.trim() === '') {
        return '-';
    }

    const normalized = normalizePhone(value);

    if (normalized === '') {
        return value;
    }

    return formatPhoneForInput(normalized);
}

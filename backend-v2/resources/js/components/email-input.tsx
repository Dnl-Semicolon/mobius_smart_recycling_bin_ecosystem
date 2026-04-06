import type { ComponentProps } from 'react';
import { useState } from 'react';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import { isValidEmail, normalizeEmail } from '@/lib/email';

type EmailInputProps = Omit<
    ComponentProps<typeof Input>,
    'onChange' | 'value'
> & {
    value: string;
    onChange: (value: string) => void;
    error?: string;
};

export default function EmailInput({
    value,
    onChange,
    error,
    className,
    onBlur,
    ...props
}: EmailInputProps) {
    const [touched, setTouched] = useState(false);
    const valid = value === '' ? false : isValidEmail(value);
    const showError = touched && value !== '' && !valid;
    const showSuccess = touched && valid && !error;

    return (
        <div className="space-y-1">
            <Input
                {...props}
                type="email"
                value={value}
                onChange={(event) => onChange(event.target.value)}
                onBlur={(event) => {
                    setTouched(true);
                    onChange(normalizeEmail(event.target.value));
                    onBlur?.(event);
                }}
                className={cn(
                    showError || error
                        ? 'border-red-500 focus-visible:ring-red-500'
                        : '',
                    showSuccess
                        ? 'border-emerald-500 focus-visible:ring-emerald-500'
                        : '',
                    className,
                )}
            />

            {!error && showError && (
                <p className="text-xs text-red-600 dark:text-red-400">
                    Please enter a valid email address.
                </p>
            )}

            {!error && showSuccess && (
                <p className="text-xs text-emerald-600 dark:text-emerald-400">
                    Looks like a valid email.
                </p>
            )}
        </div>
    );
}

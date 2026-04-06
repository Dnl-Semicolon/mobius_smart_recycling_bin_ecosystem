import type { ComponentProps } from 'react';
import { useState } from 'react';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import {
    formatPhoneForInput,
    isValidMalaysianMobile,
    MALAYSIAN_PHONE_PLACEHOLDER,
} from '@/lib/phone';

type PhoneInputProps = Omit<
    ComponentProps<typeof Input>,
    'onChange' | 'value'
> & {
    value: string;
    onChange: (value: string) => void;
    error?: string;
};

export default function PhoneInput({
    value,
    onChange,
    error,
    className,
    onBlur,
    ...props
}: PhoneInputProps) {
    const [touched, setTouched] = useState(false);
    const formattedValue = formatPhoneForInput(value);
    const showError =
        touched &&
        formattedValue !== '' &&
        !isValidMalaysianMobile(formattedValue);

    return (
        <div className="space-y-1">
            <Input
                {...props}
                type="tel"
                inputMode="numeric"
                autoComplete="tel"
                placeholder={props.placeholder ?? MALAYSIAN_PHONE_PLACEHOLDER}
                value={formattedValue}
                onChange={(event) =>
                    onChange(formatPhoneForInput(event.target.value))
                }
                onBlur={(event) => {
                    setTouched(true);
                    onChange(formatPhoneForInput(event.target.value));
                    onBlur?.(event);
                }}
                className={cn(
                    showError || error
                        ? 'border-red-500 focus-visible:ring-red-500'
                        : '',
                    className,
                )}
            />

            {!error && showError && (
                <p className="text-xs text-red-600 dark:text-red-400">
                    Please enter a valid Malaysian mobile number.
                </p>
            )}
        </div>
    );
}

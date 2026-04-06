import { Head, useForm } from '@inertiajs/react';
import { REGEXP_ONLY_DIGITS } from 'input-otp';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { logout } from '@/routes';

export default function VerifyEmailOtp({
    email,
    status,
}: {
    email: string;
    status?: string;
}) {
    const form = useForm({
        code: '',
    });

    function submit(event: React.FormEvent<HTMLFormElement>): void {
        event.preventDefault();
        form.post('/register/verify-email-otp');
    }

    return (
        <>
            <Head title="Verify email" />

            {status === 'otp-sent' && (
                <div className="mb-4 text-center text-sm font-medium text-emerald-600">
                    We sent a verification code to {email}.
                </div>
            )}

            {status === 'otp-resent' && (
                <div className="mb-4 text-center text-sm font-medium text-emerald-600">
                    A fresh verification code has been sent.
                </div>
            )}

            {status === 'otp-send-failed' && (
                <div className="mb-4 text-center text-sm font-medium text-amber-600">
                    We could not send your code automatically. Use resend to try
                    again.
                </div>
            )}

            <form className="space-y-6" onSubmit={submit}>
                <div className="flex flex-col items-center justify-center space-y-3 text-center">
                    <p className="text-sm text-muted-foreground">
                        Enter the 6-digit code sent to{' '}
                        <span className="font-medium text-foreground">
                            {email}
                        </span>
                        .
                    </p>

                    <InputOTP
                        name="code"
                        maxLength={6}
                        value={form.data.code}
                        onChange={(value) => form.setData('code', value)}
                        disabled={form.processing}
                        pattern={REGEXP_ONLY_DIGITS}
                    >
                        <InputOTPGroup>
                            {Array.from({ length: 6 }, (_, index) => (
                                <InputOTPSlot key={index} index={index} />
                            ))}
                        </InputOTPGroup>
                    </InputOTP>

                    <InputError message={form.errors.code} />
                </div>

                <div className="space-y-3">
                    <Button className="w-full" disabled={form.processing}>
                        Verify email
                    </Button>

                    <button
                        type="button"
                        className="w-full text-sm font-medium text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current dark:decoration-neutral-500"
                        onClick={() =>
                            form.post('/register/verify-email-otp/resend', {
                                preserveScroll: true,
                            })
                        }
                    >
                        Resend code
                    </button>
                </div>

                <TextLink href={logout()} className="mx-auto block text-sm">
                    Log out
                </TextLink>
            </form>
        </>
    );
}

VerifyEmailOtp.layout = {
    title: 'Verify email',
    description:
        'Enter the 6-digit verification code we sent to your email address.',
};

import { Head, Link, useForm } from '@inertiajs/react';
import { REGEXP_ONLY_DIGITS } from 'input-otp';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';

type GetStartedVerifyProps = {
    contactEmail: string;
    status?: string;
    token: string;
};

export default function GetStartedVerify({
    contactEmail,
    status,
    token,
}: GetStartedVerifyProps) {
    const form = useForm({
        code: '',
    });

    function submit(event: React.FormEvent<HTMLFormElement>): void {
        event.preventDefault();
        form.post(`/get-started/verify/${token}`);
    }

    return (
        <>
            <Head title="Verify your inquiry" />

            <div className="flex min-h-screen items-center justify-center bg-white dark:bg-gray-950">
                <div className="w-full max-w-md rounded-2xl border bg-white p-8 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
                        Verify your inquiry
                    </h1>
                    <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Enter the 6-digit code sent to{' '}
                        <span className="font-medium text-gray-900 dark:text-white">
                            {contactEmail}
                        </span>
                        .
                    </p>

                    {status === 'otp-sent' && (
                        <p className="mt-4 text-sm font-medium text-emerald-600 dark:text-emerald-400">
                            We sent a verification code to your email address.
                        </p>
                    )}

                    {status === 'otp-resent' && (
                        <p className="mt-4 text-sm font-medium text-emerald-600 dark:text-emerald-400">
                            A fresh verification code has been sent.
                        </p>
                    )}

                    {status === 'otp-send-failed' && (
                        <p className="mt-4 text-sm font-medium text-amber-600 dark:text-amber-400">
                            We could not send the code automatically. Use resend
                            to try again.
                        </p>
                    )}

                    <form className="mt-6 space-y-6" onSubmit={submit}>
                        <div className="flex flex-col items-center gap-3">
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
                                        <InputOTPSlot
                                            key={index}
                                            index={index}
                                        />
                                    ))}
                                </InputOTPGroup>
                            </InputOTP>
                            <InputError message={form.errors.code} />
                        </div>

                        <div className="space-y-3">
                            <Button
                                className="w-full"
                                disabled={form.processing}
                            >
                                Verify inquiry
                            </Button>

                            <button
                                type="button"
                                className="w-full text-sm font-medium text-emerald-700 underline underline-offset-4 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300"
                                onClick={() =>
                                    form.post(
                                        `/get-started/verify/${token}/resend`,
                                        {
                                            preserveScroll: true,
                                        },
                                    )
                                }
                            >
                                Resend code
                            </button>
                        </div>
                    </form>

                    <Link
                        href="/"
                        className="mt-6 inline-block text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        Back to home
                    </Link>
                </div>
            </div>
        </>
    );
}

import { Head, Link } from '@inertiajs/react';

export default function GetStartedConfirmation() {
    return (
        <>
            <Head title="Thank You" />
            <div className="flex min-h-screen items-center justify-center bg-white dark:bg-gray-950">
                <div className="max-w-md px-6 text-center">
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Thank you for your interest</h1>
                    <p className="mt-4 text-sm text-gray-600 dark:text-gray-400">
                        We've received your inquiry. Our team will review your application and get back to you shortly to discuss next steps.
                    </p>
                    <Link
                        href="/"
                        className="mt-8 inline-block rounded-lg border border-gray-300 px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900"
                    >
                        Back to Home
                    </Link>
                </div>
            </div>
        </>
    );
}

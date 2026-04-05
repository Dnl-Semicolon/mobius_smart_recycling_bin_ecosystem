import { Head, Link } from '@inertiajs/react';

export default function BillingSuccess() {
    return (
        <>
            <Head title="Payment Successful" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div>
                    <h1 className="text-2xl font-bold text-emerald-600">Payment Successful</h1>
                    <p className="mt-2 text-sm text-muted-foreground">
                        Your subscription payment has been processed. Your account is now fully active.
                    </p>
                </div>
                <div>
                    <Link
                        href="/brand"
                        className="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                    >
                        Go to Dashboard
                    </Link>
                </div>
            </div>
        </>
    );
}

BillingSuccess.layout = {
    breadcrumbs: [
        { title: 'Brand Dashboard', href: '/brand' },
        { title: 'Billing', href: '/brand/billing' },
        { title: 'Success', href: '#' },
    ],
};

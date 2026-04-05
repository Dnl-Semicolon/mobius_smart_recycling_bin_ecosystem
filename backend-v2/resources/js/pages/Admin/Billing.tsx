import { Head } from '@inertiajs/react';

export default function AdminBilling() {
    return (
        <>
            <Head title="Billing" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Billing</h1>
                <p className="text-sm text-muted-foreground">View all organization subscriptions and payments.</p>
                <div className="rounded-xl border border-dashed p-8 text-center">
                    <p className="text-sm text-muted-foreground">Subscription management coming soon.</p>
                </div>
            </div>
        </>
    );
}

AdminBilling.layout = {
    breadcrumbs: [
        { title: 'Admin Dashboard', href: '/admin' },
        { title: 'Billing', href: '/admin/billing' },
    ],
};

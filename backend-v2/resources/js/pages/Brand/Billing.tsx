import { Head } from '@inertiajs/react';

export default function BrandBilling() {
    return (
        <>
            <Head title="Billing" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Billing</h1>
                <p className="text-sm text-muted-foreground">Manage your subscription and payment methods.</p>
                <div className="rounded-xl border border-dashed p-8 text-center">
                    <p className="text-sm text-muted-foreground">Stripe billing integration coming soon.</p>
                </div>
            </div>
        </>
    );
}

BrandBilling.layout = {
    breadcrumbs: [
        { title: 'Brand Dashboard', href: '/brand' },
        { title: 'Billing', href: '/brand/billing' },
    ],
};

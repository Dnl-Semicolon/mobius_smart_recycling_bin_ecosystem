import { Head, Link, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Sub = {
    plan_name: string;
    price_monthly: string;
    stripe_price_id: string | null;
    status: string;
    starts_at: string;
    ends_at: string;
};

export default function BrandBilling({
    subscription,
    hasStripeSubscription,
}: {
    subscription: Sub | null;
    hasStripeSubscription: boolean;
}) {
    function handleCheckout() {
        router.post('/brand/billing/checkout');
    }

    return (
        <>
            <Head title="Billing" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div>
                    <h1 className="text-2xl font-bold">Billing</h1>
                    <p className="text-sm text-muted-foreground">Manage your subscription and payments.</p>
                </div>

                {subscription ? (
                    <div className="max-w-xl rounded-xl border p-6">
                        <div className="flex items-center justify-between">
                            <h2 className="text-lg font-semibold">{subscription.plan_name} Plan</h2>
                            <Badge variant={subscription.status === 'active' ? 'default' : 'destructive'}>
                                {subscription.status}
                            </Badge>
                        </div>
                        <p className="mt-1 text-2xl font-bold">
                            RM{parseFloat(subscription.price_monthly)}<span className="text-sm font-normal text-muted-foreground">/mo</span>
                        </p>
                        <dl className="mt-4 space-y-2 text-sm">
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">Started</dt>
                                <dd>{subscription.starts_at}</dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">Ends</dt>
                                <dd>{subscription.ends_at}</dd>
                            </div>
                        </dl>

                        <div className="mt-6 flex gap-3">
                            {hasStripeSubscription ? (
                                <Link
                                    href="/brand/billing/portal"
                                    className="rounded-lg bg-gray-900 px-6 py-2 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                                >
                                    Manage via Stripe
                                </Link>
                            ) : subscription.stripe_price_id ? (
                                <button
                                    onClick={handleCheckout}
                                    className="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                                >
                                    Pay Now via Stripe
                                </button>
                            ) : (
                                <p className="text-sm text-muted-foreground">
                                    Custom plan — contact admin for payment arrangements.
                                </p>
                            )}
                        </div>
                    </div>
                ) : (
                    <div className="max-w-xl rounded-xl border border-dashed p-6 text-center">
                        <p className="text-sm text-muted-foreground">No subscription found. Contact admin.</p>
                    </div>
                )}
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

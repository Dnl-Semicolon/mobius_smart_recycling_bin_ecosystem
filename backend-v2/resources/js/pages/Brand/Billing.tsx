import { Head, Link, usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Sub = {
    plan_name: string;
    price_monthly: string;
    stripe_price_id: string | null;
    status: string;
    starts_at: string | null;
    ends_at: string | null;
};

const statusVariant: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    active: 'default',
    pending_payment: 'outline',
    past_due: 'destructive',
    cancelled: 'secondary',
    expired: 'destructive',
};

export default function BrandBilling({
    subscription,
    hasStripeSubscription,
}: {
    subscription: Sub | null;
    hasStripeSubscription: boolean;
}) {
    const csrfToken = usePage<{ csrf_token: string }>().props.csrf_token ?? document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

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
                            <Badge variant={statusVariant[subscription.status] ?? 'secondary'}>
                                {subscription.status.replace(/_/g, ' ')}
                            </Badge>
                        </div>
                        <p className="mt-1 text-2xl font-bold">
                            RM{parseFloat(subscription.price_monthly)}<span className="text-sm font-normal text-muted-foreground">/mo</span>
                        </p>

                        {subscription.status === 'active' && subscription.starts_at && (
                            <dl className="mt-4 space-y-2 text-sm">
                                <div className="flex justify-between">
                                    <dt className="text-muted-foreground">Started</dt>
                                    <dd>{subscription.starts_at}</dd>
                                </div>
                                {subscription.ends_at && (
                                    <div className="flex justify-between">
                                        <dt className="text-muted-foreground">Ends</dt>
                                        <dd>{subscription.ends_at}</dd>
                                    </div>
                                )}
                            </dl>
                        )}

                        {subscription.status === 'pending_payment' && (
                            <p className="mt-4 text-sm text-amber-600 dark:text-amber-400">
                                Your subscription is pending payment. Complete payment to activate your account.
                            </p>
                        )}

                        <div className="mt-6 flex gap-3">
                            {subscription.status === 'pending_payment' && subscription.stripe_price_id && (
                                <form action="/brand/billing/checkout" method="POST">
                                    <input type="hidden" name="_token" value={csrfToken} />
                                    <button
                                        type="submit"
                                        className="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                                    >
                                        Pay Now via Stripe
                                    </button>
                                </form>
                            )}

                            {subscription.status === 'pending_payment' && !subscription.stripe_price_id && (
                                <p className="text-sm text-muted-foreground">
                                    Custom plan — contact admin for payment arrangements.
                                </p>
                            )}

                            {hasStripeSubscription && subscription.status === 'active' && (
                                <Link
                                    href="/brand/billing/portal"
                                    className="rounded-lg bg-gray-900 px-6 py-2 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                                >
                                    Manage via Stripe
                                </Link>
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

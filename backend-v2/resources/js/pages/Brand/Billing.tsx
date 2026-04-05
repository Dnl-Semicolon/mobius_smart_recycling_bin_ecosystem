import { Head, Link, usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';

type Sub = {
    plan_name: string;
    price_monthly: string;
    stripe_price_id: string | null;
    status: string;
    starts_at: string | null;
    ends_at: string | null;
};

type StripeData = {
    stripe_status: string;
    current_period_end: string | null;
    pm_brand: string | null;
    pm_last_four: string | null;
    next_payment_date: string | null;
    next_payment_amount: string | null;
} | null;

type Invoice = {
    id: string;
    date: string;
    amount: string;
    status: string;
    pdf_url: string | null;
    receipt_url: string | null;
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
    stripe,
    invoices,
    hasStripeSubscription,
}: {
    subscription: Sub | null;
    stripe: StripeData;
    invoices: Invoice[];
    hasStripeSubscription: boolean;
}) {
    const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

    return (
        <>
            <Head title="Billing" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div>
                    <h1 className="text-2xl font-bold">Billing</h1>
                    <p className="text-sm text-muted-foreground">Manage your subscription and payments.</p>
                </div>

                {subscription ? (
                    <div className="grid gap-6 md:grid-cols-2">
                        {/* Subscription Card */}
                        <div className="rounded-xl border p-6">
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
                                    {!subscription.ends_at && (
                                        <div className="flex justify-between">
                                            <dt className="text-muted-foreground">Renewal</dt>
                                            <dd>Auto-renewing monthly</dd>
                                        </div>
                                    )}
                                </dl>
                            )}

                            {subscription.status === 'pending_payment' && (
                                <p className="mt-4 text-sm text-amber-600 dark:text-amber-400">
                                    Your subscription is pending payment. Complete payment to activate.
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

                                {hasStripeSubscription && (
                                    <a
                                        href="/brand/billing/portal"
                                        className="rounded-lg bg-gray-900 px-6 py-2 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                                    >
                                        Manage via Stripe
                                    </a>
                                )}
                            </div>
                        </div>

                        {/* Stripe Details Card */}
                        {stripe && (
                            <div className="rounded-xl border p-6">
                                <h2 className="mb-4 text-sm font-semibold uppercase tracking-wide text-muted-foreground">Payment Details</h2>
                                <dl className="space-y-3 text-sm">
                                    <div className="flex justify-between">
                                        <dt className="text-muted-foreground">Stripe Status</dt>
                                        <dd>
                                            <Badge variant={stripe.stripe_status === 'active' ? 'default' : 'destructive'}>
                                                {stripe.stripe_status}
                                            </Badge>
                                        </dd>
                                    </div>
                                    {stripe.pm_brand && stripe.pm_last_four && (
                                        <div className="flex justify-between">
                                            <dt className="text-muted-foreground">Payment Method</dt>
                                            <dd className="font-mono">
                                                {stripe.pm_brand} •••• {stripe.pm_last_four}
                                            </dd>
                                        </div>
                                    )}
                                    {stripe.next_payment_date && (
                                        <div className="flex justify-between">
                                            <dt className="text-muted-foreground">Next Payment</dt>
                                            <dd>{stripe.next_payment_date}</dd>
                                        </div>
                                    )}
                                    {stripe.next_payment_amount && (
                                        <div className="flex justify-between">
                                            <dt className="text-muted-foreground">Next Amount</dt>
                                            <dd>RM{stripe.next_payment_amount}</dd>
                                        </div>
                                    )}
                                </dl>
                            </div>
                        )}
                    </div>
                ) : (
                    <div className="max-w-xl rounded-xl border border-dashed p-6 text-center">
                        <p className="text-sm text-muted-foreground">No subscription found. Contact admin.</p>
                    </div>
                )}

                {/* Invoice History */}
                {invoices.length > 0 && (
                    <div>
                        <h2 className="mb-4 text-lg font-semibold">Invoice History</h2>
                        <div className="rounded-xl border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Amount</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Documents</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {invoices.map((inv) => (
                                        <TableRow key={inv.id}>
                                            <TableCell>{inv.date}</TableCell>
                                            <TableCell>{inv.amount}</TableCell>
                                            <TableCell>
                                                <Badge variant={inv.status === 'paid' ? 'default' : 'outline'}>
                                                    {inv.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex gap-3">
                                                    {inv.pdf_url && (
                                                        <a
                                                            href={inv.pdf_url}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="text-sm font-medium text-emerald-600 hover:text-emerald-700"
                                                        >
                                                            Invoice PDF
                                                        </a>
                                                    )}
                                                    {inv.receipt_url && (
                                                        <a
                                                            href={inv.receipt_url}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                                                        >
                                                            Receipt
                                                        </a>
                                                    )}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
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

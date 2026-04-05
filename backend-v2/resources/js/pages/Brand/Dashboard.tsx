import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Org = { id: number; name: string; type: string };
type BrandInfo = { id: number; name: string; slug: string };
type Sub = {
    plan: string;
    price_monthly: string;
    status: string;
    starts_at: string;
    ends_at: string;
    renews_at: string;
};
type OutletInfo = { id: number; name: string; address: string; is_active: boolean };

export default function BrandDashboard({
    organization,
    brand,
    subscription,
    outlets,
}: {
    organization: Org | null;
    brand: BrandInfo | null;
    subscription: Sub | null;
    outlets: OutletInfo[];
}) {
    return (
        <>
            <Head title="Brand Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div>
                    <h1 className="text-2xl font-bold">
                        {organization ? `Welcome, ${organization.name}` : 'Brand Dashboard'}
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Manage your brand, outlets, and subscription.
                    </p>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    {/* Brand */}
                    {brand && (
                        <div className="rounded-xl border p-6">
                            <h2 className="mb-4 text-sm font-semibold uppercase tracking-wide text-muted-foreground">Your Brand</h2>
                            <p className="text-lg font-medium">{brand.name}</p>
                            <p className="mt-1 text-sm text-muted-foreground">Slug: {brand.slug}</p>
                        </div>
                    )}

                    {/* Subscription */}
                    {subscription ? (
                        <div className="rounded-xl border p-6">
                            <h2 className="mb-4 text-sm font-semibold uppercase tracking-wide text-muted-foreground">Subscription</h2>
                            <div className="flex items-center gap-2">
                                <p className="text-lg font-medium">{subscription.plan} Plan</p>
                                <Badge variant={subscription.status === 'active' ? 'default' : 'outline'}>
                                    {subscription.status.replace(/_/g, ' ')}
                                </Badge>
                            </div>
                            <p className="mt-1 text-sm text-muted-foreground">
                                RM{parseFloat(subscription.price_monthly)}/mo
                            </p>
                            {subscription.status === 'pending_payment' && (
                                <div className="mt-4">
                                    <a href="/brand/billing" className="text-sm font-medium text-emerald-600 hover:text-emerald-700">
                                        Complete payment to activate →
                                    </a>
                                </div>
                            )}
                            {subscription.status === 'active' && subscription.starts_at && (
                                <dl className="mt-4 space-y-1 text-sm">
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
                        </div>
                    ) : (
                        <div className="rounded-xl border border-dashed p-6">
                            <h2 className="mb-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">Subscription</h2>
                            <p className="text-sm text-muted-foreground">No active subscription. Contact admin to get started.</p>
                        </div>
                    )}
                </div>

                {/* Outlets */}
                <div className="rounded-xl border p-6">
                    <h2 className="mb-4 text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                        Outlets ({outlets.length})
                    </h2>
                    {outlets.length > 0 ? (
                        <div className="space-y-3">
                            {outlets.map((outlet) => (
                                <div key={outlet.id} className="flex items-center justify-between rounded-lg border px-4 py-3 text-sm">
                                    <div>
                                        <p className="font-medium">{outlet.name}</p>
                                        <p className="text-muted-foreground">{outlet.address}</p>
                                    </div>
                                    <Badge variant={outlet.is_active ? 'default' : 'secondary'}>
                                        {outlet.is_active ? 'active' : 'inactive'}
                                    </Badge>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="text-sm text-muted-foreground">
                            No outlets yet. Your first outlet will be set up during the pilot onboarding.
                        </p>
                    )}
                </div>
            </div>
        </>
    );
}

BrandDashboard.layout = {
    breadcrumbs: [{ title: 'Brand Dashboard', href: '/brand' }],
};

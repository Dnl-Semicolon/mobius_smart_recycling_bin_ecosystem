import { Head, Link, useForm } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Sub = {
    id: number;
    organization: string;
    plan: string;
    plan_bin_limit: number | null;
    plan_outlet_limit: number | null;
    plan_staff_limit: number | null;
    custom_bin_limit: number | null;
    custom_outlet_limit: number | null;
    custom_staff_limit: number | null;
    custom_price_monthly: string | null;
    billing_interval: string;
    notes: string | null;
    status: string;
};

export default function Customize({ subscription }: { subscription: Sub }) {
    const form = useForm({
        custom_bin_limit: subscription.custom_bin_limit ?? '',
        custom_outlet_limit: subscription.custom_outlet_limit ?? '',
        custom_staff_limit: subscription.custom_staff_limit ?? '',
        custom_price_monthly: subscription.custom_price_monthly ?? '',
        billing_interval: subscription.billing_interval,
        notes: subscription.notes ?? '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.patch(`/admin/billing/${subscription.id}/customize`);
    }

    return (
        <>
            <Head title={`Customize — ${subscription.organization}`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div>
                    <h1 className="text-2xl font-bold">Customize Subscription</h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {subscription.organization} — {subscription.plan} Plan
                        <Badge variant="outline" className="ml-2">{subscription.status.replace(/_/g, ' ')}</Badge>
                    </p>
                </div>

                <form onSubmit={submit} className="max-w-xl space-y-6">
                    <div>
                        <h2 className="mb-3 text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                            Limit Overrides
                        </h2>
                        <p className="mb-4 text-xs text-muted-foreground">
                            Leave empty to use plan defaults. Plan defaults: {subscription.plan_bin_limit ?? '∞'} bins, {subscription.plan_outlet_limit ?? '∞'} outlets, {subscription.plan_staff_limit ?? '∞'} staff.
                        </p>
                        <div className="grid grid-cols-3 gap-4">
                            <div>
                                <label className="block text-sm font-medium">Bin Limit</label>
                                <input
                                    type="number"
                                    value={form.data.custom_bin_limit}
                                    onChange={(e) => form.setData('custom_bin_limit', e.target.value)}
                                    className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder={String(subscription.plan_bin_limit ?? '∞')}
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium">Outlet Limit</label>
                                <input
                                    type="number"
                                    value={form.data.custom_outlet_limit}
                                    onChange={(e) => form.setData('custom_outlet_limit', e.target.value)}
                                    className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder={String(subscription.plan_outlet_limit ?? '∞')}
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium">Staff Limit</label>
                                <input
                                    type="number"
                                    value={form.data.custom_staff_limit}
                                    onChange={(e) => form.setData('custom_staff_limit', e.target.value)}
                                    className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder={String(subscription.plan_staff_limit ?? '∞')}
                                />
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 className="mb-3 text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                            Pricing
                        </h2>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium">Custom Monthly Price (RM)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    value={form.data.custom_price_monthly}
                                    onChange={(e) => form.setData('custom_price_monthly', e.target.value)}
                                    className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="Leave empty for plan price"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium">Billing Interval</label>
                                <select
                                    value={form.data.billing_interval}
                                    onChange={(e) => form.setData('billing_interval', e.target.value)}
                                    className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                >
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Internal Notes</label>
                        <textarea
                            value={form.data.notes}
                            onChange={(e) => form.setData('notes', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            rows={3}
                            placeholder="e.g. Negotiated with Sarah Lee, 2-year contract, pilot in Penang first"
                        />
                    </div>

                    <div className="flex gap-3">
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {form.processing ? 'Saving...' : 'Save Overrides'}
                        </button>
                        <Link href="/admin/billing" className="rounded-lg border px-6 py-2 text-sm font-semibold">
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </>
    );
}

Customize.layout = {
    breadcrumbs: [
        { title: 'Admin Dashboard', href: '/admin' },
        { title: 'Billing', href: '/admin/billing' },
        { title: 'Customize', href: '#' },
    ],
};

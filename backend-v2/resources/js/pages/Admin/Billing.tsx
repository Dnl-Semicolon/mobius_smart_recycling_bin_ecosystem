import { Head, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';

type Sub = {
    id: number;
    organization: string;
    plan: string;
    price_monthly: string;
    billing_interval: string;
    status: string;
    has_overrides: boolean;
    starts_at: string | null;
    ends_at: string | null;
    created_at: string;
};

type Paginated = {
    data: Sub[];
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
};

const statusVariant: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    active: 'default',
    pending_payment: 'outline',
    past_due: 'destructive',
    cancelled: 'secondary',
    expired: 'destructive',
};

export default function AdminBilling({ subscriptions }: { subscriptions: Paginated }) {
    function handleActivate(id: number) {
        if (confirm('Manually activate this subscription?')) {
            router.post(`/admin/billing/${id}/activate`);
        }
    }

    return (
        <>
            <Head title="Billing" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Billing</h1>
                    <p className="text-sm text-muted-foreground">{subscriptions.total} subscriptions</p>
                </div>

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Organization</TableHead>
                                <TableHead>Plan</TableHead>
                                <TableHead>Price</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Started</TableHead>
                                <TableHead>Ends</TableHead>
                                <TableHead>Interval</TableHead>
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {subscriptions.data.map((sub) => (
                                <TableRow key={sub.id}>
                                    <TableCell className="font-medium">{sub.organization}</TableCell>
                                    <TableCell>{sub.plan}</TableCell>
                                    <TableCell>RM{parseFloat(sub.price_monthly).toLocaleString()}/{sub.billing_interval === 'yearly' ? 'yr' : 'mo'}</TableCell>
                                    <TableCell>
                                        <Badge variant={statusVariant[sub.status] ?? 'secondary'}>
                                            {sub.status.replace(/_/g, ' ')}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{sub.starts_at ?? '-'}</TableCell>
                                    <TableCell>{sub.ends_at ?? '-'}</TableCell>
                                    <TableCell className="capitalize">{sub.billing_interval}</TableCell>
                                    <TableCell>
                                        <div className="flex gap-2">
                                            {sub.status === 'pending_payment' && (
                                                <button
                                                    onClick={() => handleActivate(sub.id)}
                                                    className="rounded border px-3 py-1 text-xs font-medium text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950"
                                                >
                                                    Activate
                                                </button>
                                            )}
                                            <a
                                                href={`/admin/billing/${sub.id}/customize`}
                                                className="rounded border px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-900"
                                            >
                                                {sub.has_overrides ? 'Edit Overrides' : 'Customize'}
                                            </a>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                            {subscriptions.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={7} className="py-8 text-center text-muted-foreground">
                                        No subscriptions yet.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
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

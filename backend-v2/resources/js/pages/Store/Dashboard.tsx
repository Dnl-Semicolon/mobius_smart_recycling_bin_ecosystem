import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type OutletInfo = {
    id: number;
    name: string;
    brand: string;
    address: string | null;
    bin_count: number;
    is_active: boolean;
};

export default function StoreDashboard({
    outlets,
    totalBins,
}: {
    outlets: OutletInfo[];
    totalBins: number;
}) {
    return (
        <>
            <Head title="Store Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Store Dashboard</h1>
                <p className="text-muted-foreground">Manage your outlets and bins.</p>
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">My Outlets</p>
                        <p className="text-3xl font-bold">{outlets.length}</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Total Bins</p>
                        <p className="text-3xl font-bold">{totalBins}</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Vouchers Redeemed</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                </div>

                {outlets.length > 0 && (
                    <div>
                        <h2 className="mb-3 text-lg font-semibold">My Outlets</h2>
                        <div className="space-y-3">
                            {outlets.map((o) => (
                                <div key={o.id} className="flex items-center justify-between rounded-xl border px-5 py-4">
                                    <div>
                                        <p className="font-medium">{o.brand} — {o.name}</p>
                                        {o.address && <p className="text-sm text-muted-foreground">{o.address}</p>}
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <span className="text-sm text-muted-foreground">{o.bin_count} bins</span>
                                        <Badge variant={o.is_active ? 'default' : 'secondary'}>
                                            {o.is_active ? 'active' : 'inactive'}
                                        </Badge>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}

StoreDashboard.layout = {
    breadcrumbs: [{ title: 'Store Dashboard', href: '/store' }],
};

import { Head } from '@inertiajs/react';

type Stats = {
    total_users: number;
    total_organizations: number;
    pending_leads: number;
    total_leads: number;
    total_bins: number;
    total_outlets: number;
};

export default function AdminDashboard({ stats }: { stats: Stats }) {
    const cards = [
        { label: 'Users', value: stats.total_users },
        { label: 'Organizations', value: stats.total_organizations },
        { label: 'Pending Leads', value: stats.pending_leads },
        { label: 'Total Leads', value: stats.total_leads },
        { label: 'Outlets', value: stats.total_outlets },
        { label: 'Bins', value: stats.total_bins },
    ];

    return (
        <>
            <Head title="Admin Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Admin Dashboard</h1>
                <p className="text-muted-foreground">System overview.</p>
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    {cards.map((card) => (
                        <div key={card.label} className="rounded-xl border p-6">
                            <p className="text-sm text-muted-foreground">{card.label}</p>
                            <p className="text-3xl font-bold">{card.value}</p>
                        </div>
                    ))}
                </div>
            </div>
        </>
    );
}

AdminDashboard.layout = {
    breadcrumbs: [{ title: 'Admin Dashboard', href: '/admin' }],
};

import { Head } from '@inertiajs/react';

type Stats = {
    pending_pickups: number;
    assigned_pickups: number;
    active_routes: number;
    completed_routes: number;
};

export default function CollectorDashboard({ stats }: { stats: Stats }) {
    return (
        <>
            <Head title="Collector Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Collector Dashboard</h1>
                <p className="text-muted-foreground">Your pickup assignments and routes.</p>
                <div className="grid auto-rows-min gap-4 md:grid-cols-4">
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Pending Pickups</p>
                        <p className="text-3xl font-bold">{stats.pending_pickups}</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Assigned to Me</p>
                        <p className="text-3xl font-bold">{stats.assigned_pickups}</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Active Routes</p>
                        <p className="text-3xl font-bold">{stats.active_routes}</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Completed Routes</p>
                        <p className="text-3xl font-bold">{stats.completed_routes}</p>
                    </div>
                </div>
            </div>
        </>
    );
}

CollectorDashboard.layout = {
    breadcrumbs: [{ title: 'Collector Dashboard', href: '/collector' }],
};

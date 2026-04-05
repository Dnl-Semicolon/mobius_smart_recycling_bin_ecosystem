import { Head } from '@inertiajs/react';

export default function CollectorDashboard() {
    return (
        <>
            <Head title="Collector Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Collector Dashboard</h1>
                <p className="text-muted-foreground">View your pickup requests and routes.</p>
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Pending Pickups</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Routes Today</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Completed Routes</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                </div>
            </div>
        </>
    );
}

CollectorDashboard.layout = {
    breadcrumbs: [{ title: 'Collector Dashboard', href: '/collector' }],
};

import { Head } from '@inertiajs/react';

export default function StoreDashboard() {
    return (
        <>
            <Head title="Store Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Store Dashboard</h1>
                <p className="text-muted-foreground">Manage your outlets and bins.</p>
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">My Outlets</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">My Bins</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Pickup Requests</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                </div>
            </div>
        </>
    );
}

StoreDashboard.layout = {
    breadcrumbs: [{ title: 'Store Dashboard', href: '/store' }],
};

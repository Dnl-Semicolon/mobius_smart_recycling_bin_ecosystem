import { Head } from '@inertiajs/react';

export default function AdminDashboard() {
    return (
        <>
            <Head title="Admin Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Admin Dashboard</h1>
                <p className="text-muted-foreground">System overview and management.</p>
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Total Users</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Active Bins</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Detection Events</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                </div>
            </div>
        </>
    );
}

AdminDashboard.layout = {
    breadcrumbs: [{ title: 'Admin Dashboard', href: '/admin' }],
};

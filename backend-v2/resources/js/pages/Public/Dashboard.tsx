import { Head } from '@inertiajs/react';

export default function PublicDashboard() {
    return (
        <>
            <Head title="My Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">My Dashboard</h1>
                <p className="text-muted-foreground">Your recycling stats and rewards.</p>
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Points Balance</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Current Streak</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Items Recycled</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                </div>
            </div>
        </>
    );
}

PublicDashboard.layout = {
    breadcrumbs: [{ title: 'My Dashboard', href: '/public' }],
};

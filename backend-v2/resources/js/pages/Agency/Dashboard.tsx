import { Head } from '@inertiajs/react';

export default function AgencyDashboard() {
    return (
        <>
            <Head title="Agency Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Agency Dashboard</h1>
                <p className="text-muted-foreground">Manage your collectors and routes.</p>
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Collectors</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Active Routes</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Fleet Size</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                </div>
            </div>
        </>
    );
}

AgencyDashboard.layout = {
    breadcrumbs: [{ title: 'Agency Dashboard', href: '/agency' }],
};

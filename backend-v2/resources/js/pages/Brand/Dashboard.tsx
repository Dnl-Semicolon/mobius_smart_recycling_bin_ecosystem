import { Head } from '@inertiajs/react';

export default function BrandDashboard() {
    return (
        <>
            <Head title="Brand Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Brand Dashboard</h1>
                <p className="text-muted-foreground">Manage your outlets, invitations, and brand analytics.</p>
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Outlets</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Invitations</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Bins Deployed</p>
                        <p className="text-3xl font-bold">--</p>
                    </div>
                </div>
            </div>
        </>
    );
}

BrandDashboard.layout = {
    breadcrumbs: [{ title: 'Brand Dashboard', href: '/brand' }],
};

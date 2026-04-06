import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';

type Pickup = {
    id: number;
    bin_id: number;
    serial: string;
    outlet: string;
    lat: number;
    lng: number;
    reason: string;
};

type Collector = { id: number; name: string };

export default function GenerateRoute({
    pendingPickups,
    collectors,
}: {
    pendingPickups: Pickup[];
    collectors: Collector[];
}) {
    const [selected, setSelected] = useState<number[]>([]);

    const form = useForm({
        collector_id: '',
        pickup_ids: [] as number[],
        origin_lat: '5.4141',
        origin_lng: '100.3288',
    });

    function togglePickup(id: number) {
        setSelected((prev) => {
            const next = prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id];
            form.setData('pickup_ids', next);
            return next;
        });
    }

    function selectAll() {
        const all = pendingPickups.map((p) => p.id);
        setSelected(all);
        form.setData('pickup_ids', all);
    }

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.post('/admin/routes');
    }

    return (
        <>
            <Head title="Generate Route" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Generate Collection Route</h1>
                <p className="text-sm text-muted-foreground">
                    Select pending pickup requests and a collector. Google Directions API will optimize the route.
                </p>

                {form.errors.route && (
                    <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{form.errors.route}</div>
                )}

                <form onSubmit={submit} className="space-y-4">
                    <div className="max-w-md">
                        <label className="block text-sm font-medium">Collector</label>
                        <select
                            value={form.data.collector_id}
                            onChange={(e) => form.setData('collector_id', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        >
                            <option value="">Select collector...</option>
                            {collectors.map((c) => (
                                <option key={c.id} value={c.id}>{c.name}</option>
                            ))}
                        </select>
                        {form.errors.collector_id && <p className="mt-1 text-xs text-red-600">{form.errors.collector_id}</p>}
                    </div>

                    <div className="grid max-w-md grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Start Latitude</label>
                            <input type="text" value={form.data.origin_lat} onChange={(e) => form.setData('origin_lat', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Start Longitude</label>
                            <input type="text" value={form.data.origin_lng} onChange={(e) => form.setData('origin_lng', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        </div>
                    </div>

                    <div>
                        <div className="flex items-center justify-between mb-2">
                            <label className="text-sm font-medium">Pending Pickups ({selected.length} selected)</label>
                            <button type="button" onClick={selectAll} className="text-xs text-emerald-600 hover:text-emerald-700">Select All</button>
                        </div>

                        {pendingPickups.length > 0 ? (
                            <div className="space-y-2 max-h-80 overflow-auto">
                                {pendingPickups.map((p) => (
                                    <label
                                        key={p.id}
                                        className={`flex items-center gap-3 rounded-lg border px-4 py-3 cursor-pointer ${selected.includes(p.id) ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950' : ''}`}
                                    >
                                        <input
                                            type="checkbox"
                                            checked={selected.includes(p.id)}
                                            onChange={() => togglePickup(p.id)}
                                            className="rounded"
                                        />
                                        <div className="flex-1 text-sm">
                                            <p className="font-medium">{p.serial} — {p.outlet}</p>
                                            <p className="text-xs text-muted-foreground">{p.reason}</p>
                                        </div>
                                        <span className="text-xs text-muted-foreground font-mono">{p.lat}, {p.lng}</span>
                                    </label>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">No pending pickup requests.</p>
                        )}
                    </div>

                    <div className="flex gap-3">
                        <button
                            type="submit"
                            disabled={form.processing || selected.length === 0 || !form.data.collector_id}
                            className="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {form.processing ? 'Generating...' : `Generate Route (${selected.length} stops)`}
                        </button>
                        <Link href="/admin/routes" className="rounded-lg border px-6 py-2 text-sm font-semibold">Cancel</Link>
                    </div>
                </form>
            </div>
        </>
    );
}

GenerateRoute.layout = {
    breadcrumbs: [
        { title: 'Admin Dashboard', href: '/admin' },
        { title: 'Routes', href: '/admin/routes' },
        { title: 'Generate', href: '/admin/routes/generate' },
    ],
};

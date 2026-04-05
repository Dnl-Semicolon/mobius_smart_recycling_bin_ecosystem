import { Head, Link, useForm } from '@inertiajs/react';

type OutletOption = { id: number; label: string };

export default function CreateBin({ outlets }: { outlets: OutletOption[] }) {
    const form = useForm({
        serial_number: '',
        outlet_id: '',
        capacity_liters: '20',
        latitude: '',
        longitude: '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.post('/admin/bins');
    }

    return (
        <>
            <Head title="Create Bin" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Create Bin</h1>

                <form onSubmit={submit} className="max-w-xl space-y-4">
                    <div>
                        <label className="block text-sm font-medium">Serial Number</label>
                        <input
                            type="text"
                            value={form.data.serial_number}
                            onChange={(e) => form.setData('serial_number', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            placeholder="e.g. MBS-SB-001"
                        />
                        {form.errors.serial_number && <p className="mt-1 text-xs text-red-600">{form.errors.serial_number}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Outlet</label>
                        <select
                            value={form.data.outlet_id}
                            onChange={(e) => form.setData('outlet_id', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        >
                            <option value="">Select outlet...</option>
                            {outlets.map((o) => (
                                <option key={o.id} value={o.id}>{o.label}</option>
                            ))}
                        </select>
                        {form.errors.outlet_id && <p className="mt-1 text-xs text-red-600">{form.errors.outlet_id}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Capacity (liters)</label>
                        <input
                            type="number"
                            value={form.data.capacity_liters}
                            onChange={(e) => form.setData('capacity_liters', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        />
                        {form.errors.capacity_liters && <p className="mt-1 text-xs text-red-600">{form.errors.capacity_liters}</p>}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Latitude <span className="text-muted-foreground">(optional)</span></label>
                            <input
                                type="text"
                                value={form.data.latitude}
                                onChange={(e) => form.setData('latitude', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="Uses outlet location if blank"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Longitude <span className="text-muted-foreground">(optional)</span></label>
                            <input
                                type="text"
                                value={form.data.longitude}
                                onChange={(e) => form.setData('longitude', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="Uses outlet location if blank"
                            />
                        </div>
                    </div>

                    <div className="flex gap-3">
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {form.processing ? 'Creating...' : 'Create Bin'}
                        </button>
                        <Link href="/admin/bins" className="rounded-lg border px-6 py-2 text-sm font-semibold">
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </>
    );
}

CreateBin.layout = {
    breadcrumbs: [
        { title: 'Admin Dashboard', href: '/admin' },
        { title: 'Bins', href: '/admin/bins' },
        { title: 'Create', href: '/admin/bins/create' },
    ],
};

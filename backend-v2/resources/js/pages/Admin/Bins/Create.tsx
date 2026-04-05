import { Head, Link, useForm } from '@inertiajs/react';

type OutletOption = { id: number; label: string };

export default function CreateBin({ outlets }: { outlets: OutletOption[] }) {
    const form = useForm({
        outlet_id: '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.post('/admin/bins');
    }

    return (
        <>
            <Head title="Create Bin" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Pair New Bin</h1>
                <p className="text-sm text-muted-foreground">
                    Assign a new bin to an outlet. Serial number will be auto-generated.
                </p>

                <form onSubmit={submit} className="max-w-xl space-y-4">
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

                    <div className="flex gap-3">
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {form.processing ? 'Pairing...' : 'Pair Bin'}
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
        { title: 'Pair', href: '/admin/bins/create' },
    ],
};

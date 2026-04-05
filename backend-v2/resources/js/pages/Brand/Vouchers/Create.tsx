import { Head, Link, useForm } from '@inertiajs/react';

export default function CreateVoucher() {
    const form = useForm({
        name: '',
        description: '',
        type: 'discount',
        value: '',
        points_required: '',
        valid_from: '',
        valid_until: '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.post('/brand/vouchers');
    }

    return (
        <>
            <Head title="Create Voucher" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Create Voucher</h1>
                <p className="text-sm text-muted-foreground">
                    Create a reward voucher for recyclers. Normal vouchers are always available — no quota, no limits.
                </p>

                <form onSubmit={submit} className="max-w-xl space-y-4">
                    <div>
                        <label className="block text-sm font-medium">Voucher Name</label>
                        <input
                            type="text"
                            value={form.data.name}
                            onChange={(e) => form.setData('name', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            placeholder="e.g. RM5 Off Any Drink"
                        />
                        {form.errors.name && <p className="mt-1 text-xs text-red-600">{form.errors.name}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Description <span className="text-muted-foreground">(optional)</span></label>
                        <textarea
                            value={form.data.description}
                            onChange={(e) => form.setData('description', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            rows={2}
                            placeholder="e.g. Get RM5 off any handcrafted beverage"
                        />
                    </div>

                    <div className="grid grid-cols-3 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Type</label>
                            <select
                                value={form.data.type}
                                onChange={(e) => form.setData('type', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                                <option value="discount">Discount</option>
                                <option value="free_item">Free Item</option>
                                <option value="cashback">Cashback</option>
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Value (RM)</label>
                            <input
                                type="number"
                                step="0.01"
                                value={form.data.value}
                                onChange={(e) => form.setData('value', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="5.00"
                            />
                            {form.errors.value && <p className="mt-1 text-xs text-red-600">{form.errors.value}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Points Required</label>
                            <input
                                type="number"
                                value={form.data.points_required}
                                onChange={(e) => form.setData('points_required', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="170"
                            />
                            {form.errors.points_required && <p className="mt-1 text-xs text-red-600">{form.errors.points_required}</p>}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Valid From</label>
                            <input
                                type="date"
                                value={form.data.valid_from}
                                onChange={(e) => form.setData('valid_from', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                            {form.errors.valid_from && <p className="mt-1 text-xs text-red-600">{form.errors.valid_from}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Valid Until</label>
                            <input
                                type="date"
                                value={form.data.valid_until}
                                onChange={(e) => form.setData('valid_until', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                            {form.errors.valid_until && <p className="mt-1 text-xs text-red-600">{form.errors.valid_until}</p>}
                        </div>
                    </div>

                    <div className="flex gap-3">
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {form.processing ? 'Creating...' : 'Create Voucher'}
                        </button>
                        <Link href="/brand/vouchers" className="rounded-lg border px-6 py-2 text-sm font-semibold">
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </>
    );
}

CreateVoucher.layout = {
    breadcrumbs: [
        { title: 'Brand Dashboard', href: '/brand' },
        { title: 'Vouchers', href: '/brand/vouchers' },
        { title: 'Create', href: '/brand/vouchers/create' },
    ],
};

import { Head, Link, useForm } from '@inertiajs/react';
import AddressInput from '@/components/address-input';

type UserOption = { id: number; name: string };

export default function CreateBrandOutlet({ users }: { users: UserOption[] }) {
    const form = useForm({
        user_id: '',
        name: '',
        address: '',
        street: '',
        city: '',
        state: '',
        postcode: '',
        country: 'Malaysia',
        latitude: '',
        longitude: '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.post('/brand/outlets');
    }

    return (
        <>
            <Head title="Create Outlet" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Create Outlet</h1>

                <form onSubmit={submit} className="max-w-xl space-y-4">
                    <div>
                        <label className="block text-sm font-medium">Outlet Manager</label>
                        <select
                            value={form.data.user_id}
                            onChange={(e) => form.setData('user_id', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        >
                            <option value="">Select manager...</option>
                            {users.map((u) => (
                                <option key={u.id} value={u.id}>{u.name}</option>
                            ))}
                        </select>
                        {form.errors.user_id && <p className="mt-1 text-xs text-red-600">{form.errors.user_id}</p>}
                    </div>

                    <AddressInput
                        onPlaceSelect={(place) => {
                            form.setData((prev: typeof form.data) => ({
                                ...prev,
                                name: prev.name || place.name,
                                address: place.address,
                                street: place.street,
                                city: place.city,
                                state: place.state,
                                postcode: place.postcode,
                                country: place.country || 'Malaysia',
                                latitude: String(place.lat),
                                longitude: String(place.lng),
                            }));
                        }}
                    />

                    <div>
                        <label className="block text-sm font-medium">Outlet Name</label>
                        <input
                            type="text"
                            value={form.data.name}
                            onChange={(e) => form.setData('name', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            placeholder="Auto-filled from Google or type manually"
                        />
                        {form.errors.name && <p className="mt-1 text-xs text-red-600">{form.errors.name}</p>}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Street</label>
                            <input type="text" value={form.data.street} onChange={(e) => form.setData('street', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium">City</label>
                            <input type="text" value={form.data.city} onChange={(e) => form.setData('city', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium">State</label>
                            <input type="text" value={form.data.state} onChange={(e) => form.setData('state', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Postcode</label>
                            <input type="text" value={form.data.postcode} onChange={(e) => form.setData('postcode', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-muted-foreground">Latitude</label>
                            <input type="text" value={form.data.latitude} readOnly
                                className="mt-1 block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-muted-foreground">Longitude</label>
                            <input type="text" value={form.data.longitude} readOnly
                                className="mt-1 block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400" />
                        </div>
                    </div>

                    <div className="flex gap-3">
                        <button type="submit" disabled={form.processing}
                            className="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50">
                            {form.processing ? 'Creating...' : 'Create Outlet'}
                        </button>
                        <Link href="/brand/outlets" className="rounded-lg border px-6 py-2 text-sm font-semibold">Cancel</Link>
                    </div>
                </form>
            </div>
        </>
    );
}

CreateBrandOutlet.layout = {
    breadcrumbs: [
        { title: 'Brand Dashboard', href: '/brand' },
        { title: 'Outlets', href: '/brand/outlets' },
        { title: 'Create', href: '/brand/outlets/create' },
    ],
};

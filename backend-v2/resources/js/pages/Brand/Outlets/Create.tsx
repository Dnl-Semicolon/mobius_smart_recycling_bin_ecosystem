import { Head, Link, useForm } from '@inertiajs/react';
import AddressInput from '@/components/address-input';

type UserOption = { id: number; name: string };

export default function CreateBrandOutlet({ users }: { users: UserOption[] }) {
    const form = useForm({
        user_id: '',
        name: '',
        address: '',
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

                    <div>
                        <label className="block text-sm font-medium">Outlet Name</label>
                        <input
                            type="text"
                            value={form.data.name}
                            onChange={(e) => form.setData('name', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            placeholder="e.g. Starbucks Hillside Tg Bungah"
                        />
                        {form.errors.name && <p className="mt-1 text-xs text-red-600">{form.errors.name}</p>}
                    </div>

                    <AddressInput
                        value={form.data.address}
                        onChange={(v) => form.setData('address', v)}
                        onPlaceSelect={(place) => {
                            form.setData((prev: typeof form.data) => ({
                                ...prev,
                                name: prev.name || place.name,
                                address: place.address,
                                latitude: String(place.lat),
                                longitude: String(place.lng),
                            }));
                        }}
                    />
                    {form.errors.address && <p className="mt-1 text-xs text-red-600">{form.errors.address}</p>}

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium">Latitude</label>
                            <input
                                type="text"
                                value={form.data.latitude}
                                onChange={(e) => form.setData('latitude', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="e.g. 5.4575"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium">Longitude</label>
                            <input
                                type="text"
                                value={form.data.longitude}
                                onChange={(e) => form.setData('longitude', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="e.g. 100.2895"
                            />
                        </div>
                    </div>

                    <div className="flex gap-3">
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {form.processing ? 'Creating...' : 'Create Outlet'}
                        </button>
                        <Link href="/brand/outlets" className="rounded-lg border px-6 py-2 text-sm font-semibold">
                            Cancel
                        </Link>
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

import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect, useMemo, useRef } from 'react';

type BrandOption = { id: number; name: string; organization_id: number };
type UserOption = { id: number; name: string; organization_id: number };

export default function CreateOutlet({
    brands,
    users,
}: {
    brands: BrandOption[];
    users: UserOption[];
}) {
    const addressRef = useRef<HTMLInputElement>(null);
    const form = useForm({
        brand_id: '',
        user_id: '',
        name: '',
        address: '',
        latitude: '',
        longitude: '',
    });

    // Google Places autocomplete (graceful degradation)
    useEffect(() => {
        const interval = setInterval(() => {
            if (!addressRef.current || !window.google?.maps?.places) return;
            clearInterval(interval);
            try {
                const autocomplete = new window.google.maps.places.Autocomplete(addressRef.current, {
                    componentRestrictions: { country: 'my' },
                    fields: ['formatted_address', 'geometry'],
                });
                autocomplete.addListener('place_changed', () => {
                    const place = autocomplete.getPlace();
                    if (place.geometry?.location) {
                        form.setData((prev) => ({
                            ...prev,
                            address: place.formatted_address ?? '',
                            latitude: String(place.geometry!.location!.lat()),
                            longitude: String(place.geometry!.location!.lng()),
                        }));
                    }
                });
            } catch { /* Google Places unavailable, form still works */ }
        }, 500);
        return () => clearInterval(interval);
    }, []);

    // Scope owners to selected brand's organization
    const selectedBrand = brands.find((b) => String(b.id) === form.data.brand_id);
    const scopedUsers = useMemo(() => {
        if (!selectedBrand) return [];
        return users.filter((u) => u.organization_id === selectedBrand.organization_id);
    }, [selectedBrand, users]);

    // Reset user when brand changes
    useEffect(() => {
        form.setData('user_id', '');
    }, [form.data.brand_id]);

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.post('/admin/outlets');
    }

    return (
        <>
            <Head title="Create Outlet" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Create Outlet</h1>

                <form onSubmit={submit} className="max-w-xl space-y-4">
                    <div>
                        <label className="block text-sm font-medium">Brand</label>
                        <select
                            value={form.data.brand_id}
                            onChange={(e) => form.setData('brand_id', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        >
                            <option value="">Select brand...</option>
                            {brands.map((b) => (
                                <option key={b.id} value={b.id}>{b.name}</option>
                            ))}
                        </select>
                        {form.errors.brand_id && <p className="mt-1 text-xs text-red-600">{form.errors.brand_id}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Outlet Manager</label>
                        <select
                            value={form.data.user_id}
                            onChange={(e) => form.setData('user_id', e.target.value)}
                            disabled={!form.data.brand_id}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        >
                            <option value="">{form.data.brand_id ? 'Select manager...' : 'Select a brand first'}</option>
                            {scopedUsers.map((u) => (
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

                    <div>
                        <label className="block text-sm font-medium">Address</label>
                        <input
                            ref={addressRef}
                            type="text"
                            value={form.data.address}
                            onChange={(e) => form.setData('address', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            placeholder="Type address or search via Google Places"
                        />
                        {form.errors.address && <p className="mt-1 text-xs text-red-600">{form.errors.address}</p>}
                    </div>

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
                        <Link href="/admin/outlets" className="rounded-lg border px-6 py-2 text-sm font-semibold">
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </>
    );
}

CreateOutlet.layout = {
    breadcrumbs: [
        { title: 'Admin Dashboard', href: '/admin' },
        { title: 'Outlets', href: '/admin/outlets' },
        { title: 'Create', href: '/admin/outlets/create' },
    ],
};

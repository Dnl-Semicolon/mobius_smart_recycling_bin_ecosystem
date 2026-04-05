import { Head, Link, useForm } from '@inertiajs/react';

type Org = { id: number; name: string };

export default function CreateUser({ organizations }: { organizations: Org[] }) {
    const form = useForm({
        name: '',
        email: '',
        phone: '',
        role: 'store_owner',
        organization_id: '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.post('/admin/users');
    }

    return (
        <>
            <Head title="Create User" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Create User</h1>
                <p className="text-sm text-muted-foreground">
                    Create a user account. A password will be auto-generated.
                </p>

                <form onSubmit={submit} className="max-w-xl space-y-4">
                    <div>
                        <label className="block text-sm font-medium">Full Name</label>
                        <input
                            type="text"
                            value={form.data.name}
                            onChange={(e) => form.setData('name', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        />
                        {form.errors.name && <p className="mt-1 text-xs text-red-600">{form.errors.name}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Email</label>
                        <input
                            type="email"
                            value={form.data.email}
                            onChange={(e) => form.setData('email', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        />
                        {form.errors.email && <p className="mt-1 text-xs text-red-600">{form.errors.email}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Phone <span className="text-muted-foreground">(optional)</span></label>
                        <input
                            type="text"
                            value={form.data.phone}
                            onChange={(e) => form.setData('phone', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        />
                        {form.errors.phone && <p className="mt-1 text-xs text-red-600">{form.errors.phone}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Role</label>
                        <select
                            value={form.data.role}
                            onChange={(e) => form.setData('role', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        >
                            <option value="brand_owner">Brand Owner</option>
                            <option value="store_owner">Store Owner</option>
                            <option value="collector">Collector</option>
                            <option value="public_user">Public User</option>
                        </select>
                        {form.errors.role && <p className="mt-1 text-xs text-red-600">{form.errors.role}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium">Organization <span className="text-muted-foreground">(optional)</span></label>
                        <select
                            value={form.data.organization_id}
                            onChange={(e) => form.setData('organization_id', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        >
                            <option value="">No organization</option>
                            {organizations.map((org) => (
                                <option key={org.id} value={org.id}>{org.name}</option>
                            ))}
                        </select>
                        {form.errors.organization_id && <p className="mt-1 text-xs text-red-600">{form.errors.organization_id}</p>}
                    </div>

                    <div className="flex gap-3">
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {form.processing ? 'Creating...' : 'Create User'}
                        </button>
                        <Link href="/admin/users" className="rounded-lg border px-6 py-2 text-sm font-semibold">
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </>
    );
}

CreateUser.layout = {
    breadcrumbs: [
        { title: 'Admin Dashboard', href: '/admin' },
        { title: 'Users', href: '/admin/users' },
        { title: 'Create', href: '/admin/users/create' },
    ],
};

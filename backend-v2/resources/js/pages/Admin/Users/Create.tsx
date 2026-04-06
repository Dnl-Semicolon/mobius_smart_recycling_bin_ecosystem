import { Head, Link, useForm } from '@inertiajs/react';
import EmailInput from '@/components/email-input';
import InputError from '@/components/input-error';
import PhoneInput from '@/components/phone-input';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Org = { id: number; name: string };

export default function CreateUser({
    organizations,
}: {
    organizations: Org[];
}) {
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
                        <Label className="block text-sm font-medium">
                            Full Name
                        </Label>
                        <Input
                            type="text"
                            value={form.data.name}
                            onChange={(e) =>
                                form.setData('name', e.target.value)
                            }
                            className="mt-1"
                        />
                        <InputError
                            className="mt-1"
                            message={form.errors.name}
                        />
                    </div>

                    <div>
                        <Label className="block text-sm font-medium">
                            Email
                        </Label>
                        <EmailInput
                            value={form.data.email}
                            onChange={(value) => form.setData('email', value)}
                            className="mt-1"
                            placeholder="email@example.com"
                            autoComplete="email"
                            error={form.errors.email}
                        />
                        <InputError
                            className="mt-1"
                            message={form.errors.email}
                        />
                    </div>

                    <div>
                        <Label className="block text-sm font-medium">
                            Phone{' '}
                            <span className="text-muted-foreground">
                                (optional)
                            </span>
                        </Label>
                        <PhoneInput
                            value={form.data.phone}
                            onChange={(value) => form.setData('phone', value)}
                            className="mt-1"
                            error={form.errors.phone}
                        />
                        <InputError
                            className="mt-1"
                            message={form.errors.phone}
                        />
                    </div>

                    <div>
                        <Label className="block text-sm font-medium">
                            Role
                        </Label>
                        <select
                            value={form.data.role}
                            onChange={(e) =>
                                form.setData('role', e.target.value)
                            }
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        >
                            <option value="brand_owner">Brand Owner</option>
                            <option value="store_owner">Store Owner</option>
                            <option value="collector">Collector</option>
                            <option value="public_user">Public User</option>
                        </select>
                        <InputError
                            className="mt-1"
                            message={form.errors.role}
                        />
                    </div>

                    <div>
                        <Label className="block text-sm font-medium">
                            Organization{' '}
                            <span className="text-muted-foreground">
                                (optional)
                            </span>
                        </Label>
                        <select
                            value={form.data.organization_id}
                            onChange={(e) =>
                                form.setData('organization_id', e.target.value)
                            }
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        >
                            <option value="">No organization</option>
                            {organizations.map((org) => (
                                <option key={org.id} value={org.id}>
                                    {org.name}
                                </option>
                            ))}
                        </select>
                        <InputError
                            className="mt-1"
                            message={form.errors.organization_id}
                        />
                    </div>

                    <div className="flex gap-3">
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {form.processing ? 'Creating...' : 'Create User'}
                        </button>
                        <Link
                            href="/admin/users"
                            className="rounded-lg border px-6 py-2 text-sm font-semibold"
                        >
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

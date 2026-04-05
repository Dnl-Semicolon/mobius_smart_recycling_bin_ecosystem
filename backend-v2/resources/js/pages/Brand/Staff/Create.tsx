import { Head, Link, useForm } from '@inertiajs/react';
import LimitBanner from '@/components/limit-banner';

type LimitInfo = { current: number; max: number | null; reached: boolean; unlimited: boolean };

export default function CreateStaff({ limitInfo }: { limitInfo: LimitInfo | null }) {
    const form = useForm({
        name: '',
        email: '',
        phone: '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.post('/brand/staff');
    }

    return (
        <>
            <Head title="Add Staff" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">Add Staff</h1>
                <p className="text-sm text-muted-foreground">
                    Create an outlet manager account. They will be assigned the store owner role within your organization. A password will be auto-generated.
                </p>

                <LimitBanner label="Staff" limitInfo={limitInfo} />

                <form onSubmit={submit} className={`max-w-xl space-y-4 ${limitInfo?.reached ? 'pointer-events-none opacity-50' : ''}`}>
                    <div>
                        <label className="block text-sm font-medium">Full Name</label>
                        <input
                            type="text"
                            value={form.data.name}
                            onChange={(e) => form.setData('name', e.target.value)}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            placeholder="e.g. Adrian Gooi Khai Yi"
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
                            placeholder="adrian@starbucks.com.my"
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
                            placeholder="0121234567"
                        />
                        {form.errors.phone && <p className="mt-1 text-xs text-red-600">{form.errors.phone}</p>}
                    </div>

                    <div className="flex gap-3">
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {form.processing ? 'Creating...' : 'Create Account'}
                        </button>
                        <Link href="/brand/staff" className="rounded-lg border px-6 py-2 text-sm font-semibold">
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </>
    );
}

CreateStaff.layout = {
    breadcrumbs: [
        { title: 'Brand Dashboard', href: '/brand' },
        { title: 'Staff', href: '/brand/staff' },
        { title: 'Add', href: '/brand/staff/create' },
    ],
};

import { Head, Link, useForm } from '@inertiajs/react';
import EmailInput from '@/components/email-input';
import InputError from '@/components/input-error';
import LimitBanner from '@/components/limit-banner';
import PhoneInput from '@/components/phone-input';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type LimitInfo = {
    current: number;
    max: number | null;
    reached: boolean;
    unlimited: boolean;
};

export default function CreateStaff({
    limitInfo,
}: {
    limitInfo: LimitInfo | null;
}) {
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
                    Create an outlet manager account. They will be assigned the
                    store owner role within your organization. A password will
                    be auto-generated.
                </p>

                <LimitBanner label="Staff" limitInfo={limitInfo} />

                <form
                    onSubmit={submit}
                    className={`max-w-xl space-y-4 ${limitInfo?.reached ? 'pointer-events-none opacity-50' : ''}`}
                >
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
                            placeholder="e.g. Adrian Gooi Khai Yi"
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
                            placeholder="adrian@starbucks.com.my"
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
                            placeholder="0121234567"
                            error={form.errors.phone}
                        />
                        <InputError
                            className="mt-1"
                            message={form.errors.phone}
                        />
                    </div>

                    <div className="flex gap-3">
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {form.processing ? 'Creating...' : 'Create Account'}
                        </button>
                        <Link
                            href="/brand/staff"
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

CreateStaff.layout = {
    breadcrumbs: [
        { title: 'Brand Dashboard', href: '/brand' },
        { title: 'Staff', href: '/brand/staff' },
        { title: 'Add', href: '/brand/staff/create' },
    ],
};

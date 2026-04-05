import { Head, Link } from '@inertiajs/react';

export default function LeadConverted({
    lead,
    organization,
    brand,
    user_email,
    user_password,
    plan,
}: {
    lead: string;
    organization: string;
    brand: string;
    user_email: string;
    user_password: string;
    plan: string | null;
}) {
    return (
        <>
            <Head title="Lead Converted" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div>
                    <h1 className="text-2xl font-bold text-emerald-600">Lead Converted Successfully</h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        "{lead}" has been converted to a brand. The following entities were created:
                    </p>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <div className="rounded-xl border p-6">
                        <h2 className="mb-3 text-sm font-semibold uppercase tracking-wide text-muted-foreground">Organization</h2>
                        <p className="text-lg font-medium">{organization}</p>
                    </div>

                    <div className="rounded-xl border p-6">
                        <h2 className="mb-3 text-sm font-semibold uppercase tracking-wide text-muted-foreground">Brand</h2>
                        <p className="text-lg font-medium">{brand}</p>
                    </div>

                    {plan && (
                        <div className="rounded-xl border p-6">
                            <h2 className="mb-3 text-sm font-semibold uppercase tracking-wide text-muted-foreground">Subscription</h2>
                            <p className="text-lg font-medium">{plan} Plan</p>
                            <p className="text-sm text-muted-foreground">Active — 1 year term</p>
                        </div>
                    )}

                    <div className="rounded-xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-800 dark:bg-amber-950">
                        <h2 className="mb-3 text-sm font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-400">Brand Owner Credentials</h2>
                        <dl className="space-y-2 text-sm">
                            <div>
                                <dt className="text-muted-foreground">Email</dt>
                                <dd className="font-mono font-medium">{user_email}</dd>
                            </div>
                            <div>
                                <dt className="text-muted-foreground">Password</dt>
                                <dd className="font-mono font-medium">{user_password}</dd>
                            </div>
                        </dl>
                        <p className="mt-3 text-xs text-amber-600 dark:text-amber-500">
                            Send these credentials to the brand owner. They can change their password after first login.
                        </p>
                    </div>
                </div>

                <div className="flex gap-3">
                    <Link
                        href="/admin/leads"
                        className="rounded-lg border border-gray-300 px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900"
                    >
                        Back to Leads
                    </Link>
                </div>
            </div>
        </>
    );
}

LeadConverted.layout = {
    breadcrumbs: [
        { title: 'Admin Dashboard', href: '/admin' },
        { title: 'Leads', href: '/admin/leads' },
        { title: 'Converted', href: '#' },
    ],
};

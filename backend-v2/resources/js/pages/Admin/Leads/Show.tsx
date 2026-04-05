import { Head, Link, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Lead = {
    id: number;
    company_name: string;
    contact_name: string;
    contact_email: string;
    contact_phone: string;
    type: string;
    description: string | null;
    selected_plan: { id: number; name: string; price_monthly: string } | null;
    status: string;
    admin_notes: string | null;
    reviewed_by: string | null;
    reviewed_at: string | null;
    created_at: string;
};

const statusVariant: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    pending: 'outline',
    approved: 'default',
    rejected: 'destructive',
};

export default function LeadShow({ lead }: { lead: Lead }) {
    const isPending = lead.status === 'pending';

    function handleConvert() {
        if (confirm(`Convert "${lead.company_name}" to a brand? This will create an organization, brand, and user account.`)) {
            router.post(`/admin/leads/${lead.id}/convert`);
        }
    }

    function handleReject() {
        if (confirm(`Reject "${lead.company_name}"? This cannot be undone.`)) {
            router.post(`/admin/leads/${lead.id}/reject`);
        }
    }

    return (
        <>
            <Head title={`Lead: ${lead.company_name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">{lead.company_name}</h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Submitted {lead.created_at}
                        </p>
                    </div>
                    <Badge variant={statusVariant[lead.status] ?? 'secondary'} className="text-sm">
                        {lead.status}
                    </Badge>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    {/* Contact Info */}
                    <div className="rounded-xl border p-6">
                        <h2 className="mb-4 text-sm font-semibold uppercase tracking-wide text-muted-foreground">Contact</h2>
                        <dl className="space-y-3 text-sm">
                            <div>
                                <dt className="text-muted-foreground">Name</dt>
                                <dd className="font-medium">{lead.contact_name}</dd>
                            </div>
                            <div>
                                <dt className="text-muted-foreground">Email</dt>
                                <dd className="font-medium">{lead.contact_email}</dd>
                            </div>
                            <div>
                                <dt className="text-muted-foreground">Phone</dt>
                                <dd className="font-medium">{lead.contact_phone}</dd>
                            </div>
                        </dl>
                    </div>

                    {/* Business Info */}
                    <div className="rounded-xl border p-6">
                        <h2 className="mb-4 text-sm font-semibold uppercase tracking-wide text-muted-foreground">Business</h2>
                        <dl className="space-y-3 text-sm">
                            <div>
                                <dt className="text-muted-foreground">Type</dt>
                                <dd className="font-medium">{lead.type.replace(/_/g, ' ')}</dd>
                            </div>
                            <div>
                                <dt className="text-muted-foreground">Selected Plan</dt>
                                <dd className="font-medium">
                                    {lead.selected_plan
                                        ? `${lead.selected_plan.name} (RM${parseFloat(lead.selected_plan.price_monthly)}/mo)`
                                        : 'Not selected'}
                                </dd>
                            </div>
                            {lead.description && (
                                <div>
                                    <dt className="text-muted-foreground">Description</dt>
                                    <dd className="font-medium">{lead.description}</dd>
                                </div>
                            )}
                        </dl>
                    </div>
                </div>

                {/* Admin Notes */}
                {lead.admin_notes && (
                    <div className="rounded-xl border p-6">
                        <h2 className="mb-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">Admin Notes</h2>
                        <p className="text-sm">{lead.admin_notes}</p>
                    </div>
                )}

                {/* Review Info (if already reviewed) */}
                {lead.reviewed_by && (
                    <div className="rounded-xl border p-6">
                        <h2 className="mb-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">Review</h2>
                        <p className="text-sm">
                            Reviewed by <span className="font-medium">{lead.reviewed_by}</span> on {lead.reviewed_at}
                        </p>
                    </div>
                )}

                {/* Actions */}
                {isPending && (
                    <div className="flex gap-3">
                        <button
                            onClick={handleConvert}
                            className="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700"
                        >
                            Convert to Brand
                        </button>
                        <button
                            onClick={handleReject}
                            className="rounded-lg border border-red-300 px-6 py-3 text-sm font-semibold text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-950"
                        >
                            Reject
                        </button>
                    </div>
                )}

                <Link
                    href="/admin/leads"
                    className="text-sm text-muted-foreground hover:text-foreground"
                >
                    &larr; Back to Leads
                </Link>
            </div>
        </>
    );
}

LeadShow.layout = {
    breadcrumbs: [
        { title: 'Admin Dashboard', href: '/admin' },
        { title: 'Leads', href: '/admin/leads' },
        { title: 'Detail', href: '#' },
    ],
};

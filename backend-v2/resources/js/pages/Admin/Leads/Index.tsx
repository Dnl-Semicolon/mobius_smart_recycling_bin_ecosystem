import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { formatPhoneForDisplay } from '@/lib/phone';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

type Lead = {
    id: number;
    company_name: string;
    contact_name: string;
    contact_email: string;
    contact_phone: string;
    type: string;
    selected_plan: string | null;
    status: string;
    created_at: string;
};

type PaginatedLeads = {
    data: Lead[];
    current_page: number;
    last_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

const statusVariant: Record<
    string,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    pending: 'outline',
    approved: 'default',
    rejected: 'destructive',
};

export default function LeadsIndex({ leads }: { leads: PaginatedLeads }) {
    return (
        <>
            <Head title="Leads" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Leads</h1>
                    <p className="text-sm text-muted-foreground">
                        {leads.total} total
                    </p>
                </div>

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="w-12">ID</TableHead>
                                <TableHead>Company</TableHead>
                                <TableHead>Contact</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Phone</TableHead>
                                <TableHead>Plan</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Submitted</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {leads.data.map((lead) => (
                                <TableRow
                                    key={lead.id}
                                    className="cursor-pointer"
                                    onClick={() =>
                                        (window.location.href = `/admin/leads/${lead.id}`)
                                    }
                                >
                                    <TableCell className="font-mono text-sm">
                                        {lead.id}
                                    </TableCell>
                                    <TableCell className="font-medium">
                                        {lead.company_name}
                                    </TableCell>
                                    <TableCell>{lead.contact_name}</TableCell>
                                    <TableCell>{lead.contact_email}</TableCell>
                                    <TableCell>
                                        {formatPhoneForDisplay(
                                            lead.contact_phone,
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        {lead.selected_plan ?? '-'}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            variant={
                                                statusVariant[lead.status] ??
                                                'secondary'
                                            }
                                        >
                                            {lead.status}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{lead.created_at}</TableCell>
                                </TableRow>
                            ))}
                            {leads.data.length === 0 && (
                                <TableRow>
                                    <TableCell
                                        colSpan={8}
                                        className="py-8 text-center text-muted-foreground"
                                    >
                                        No leads yet.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </div>

                {leads.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2">
                        {leads.links.map((link, i) => (
                            <Link
                                key={i}
                                href={link.url ?? '#'}
                                className={`rounded-md border px-3 py-1 text-sm ${
                                    link.active
                                        ? 'bg-primary text-primary-foreground'
                                        : ''
                                } ${!link.url ? 'pointer-events-none opacity-50' : ''}`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                preserveState
                            />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

LeadsIndex.layout = {
    breadcrumbs: [
        { title: 'Admin Dashboard', href: '/admin' },
        { title: 'Leads', href: '/admin/leads' },
    ],
};

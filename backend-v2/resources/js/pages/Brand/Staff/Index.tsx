import { Head, Link, usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';

type StaffMember = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    roles: string[];
    created_at: string;
};

export default function StaffIndex({ staff }: { staff: StaffMember[] }) {
    const { flash } = usePage<{ flash: { generated_password?: string } }>().props;

    return (
        <>
            <Head title="Staff" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Staff</h1>
                    <Link
                        href="/brand/staff/create"
                        className="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                    >
                        Add Staff
                    </Link>
                </div>

                {flash?.generated_password && (
                    <div className="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950">
                        <p className="text-sm font-medium text-amber-700 dark:text-amber-400">
                            Staff account created. Generated password: <span className="font-mono">{flash.generated_password}</span>
                        </p>
                        <p className="mt-1 text-xs text-amber-600 dark:text-amber-500">
                            Share this with the staff member. They can change it after first login.
                        </p>
                    </div>
                )}

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Phone</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Added</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {staff.map((s) => (
                                <TableRow key={s.id}>
                                    <TableCell className="font-medium">{s.name}</TableCell>
                                    <TableCell>{s.email}</TableCell>
                                    <TableCell>{s.phone ?? '-'}</TableCell>
                                    <TableCell>
                                        <div className="flex gap-1">
                                            {s.roles.map((r) => (
                                                <Badge key={r} variant="secondary">{r.replace(/_/g, ' ')}</Badge>
                                            ))}
                                        </div>
                                    </TableCell>
                                    <TableCell>{s.created_at}</TableCell>
                                </TableRow>
                            ))}
                            {staff.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="py-8 text-center text-muted-foreground">
                                        No staff yet. Add your first outlet manager.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </div>
            </div>
        </>
    );
}

StaffIndex.layout = {
    breadcrumbs: [
        { title: 'Brand Dashboard', href: '/brand' },
        { title: 'Staff', href: '/brand/staff' },
    ],
};

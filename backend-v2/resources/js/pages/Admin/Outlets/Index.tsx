import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';

type Outlet = {
    id: number;
    name: string;
    address: string;
    brand: string;
    store_owner: string;
    bin_count: number;
    is_active: boolean;
    created_at: string;
};

type Paginated = {
    data: Outlet[];
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
};

export default function OutletsIndex({ outlets }: { outlets: Paginated }) {
    return (
        <>
            <Head title="Outlets" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Outlets</h1>
                    <Link
                        href="/admin/outlets/create"
                        className="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                    >
                        Create Outlet
                    </Link>
                </div>

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Brand</TableHead>
                                <TableHead>Owner</TableHead>
                                <TableHead>Bins</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Created</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {outlets.data.map((o) => (
                                <TableRow key={o.id}>
                                    <TableCell className="font-medium">{o.name}</TableCell>
                                    <TableCell>{o.brand}</TableCell>
                                    <TableCell>{o.store_owner}</TableCell>
                                    <TableCell>{o.bin_count}</TableCell>
                                    <TableCell>
                                        <Badge variant={o.is_active ? 'default' : 'secondary'}>
                                            {o.is_active ? 'active' : 'inactive'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{o.created_at}</TableCell>
                                </TableRow>
                            ))}
                            {outlets.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={6} className="py-8 text-center text-muted-foreground">
                                        No outlets yet.
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

OutletsIndex.layout = {
    breadcrumbs: [
        { title: 'Admin Dashboard', href: '/admin' },
        { title: 'Outlets', href: '/admin/outlets' },
    ],
};

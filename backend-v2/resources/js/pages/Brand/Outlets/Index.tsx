import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';

type Outlet = {
    id: number;
    name: string;
    address: string;
    manager: string;
    bin_count: number;
    is_active: boolean;
};

type Paginated = {
    data: Outlet[];
    total: number;
};

export default function BrandOutletsIndex({ outlets, brandName }: { outlets: Paginated; brandName: string }) {
    return (
        <>
            <Head title="Outlets" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Outlets</h1>
                        <p className="text-sm text-muted-foreground">{brandName}</p>
                    </div>
                    <Link
                        href="/brand/outlets/create"
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
                                <TableHead>Address</TableHead>
                                <TableHead>Manager</TableHead>
                                <TableHead>Bins</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {outlets.data.map((o) => (
                                <TableRow key={o.id}>
                                    <TableCell className="font-medium">{o.name}</TableCell>
                                    <TableCell className="max-w-xs truncate">{o.address}</TableCell>
                                    <TableCell>{o.manager}</TableCell>
                                    <TableCell>{o.bin_count}</TableCell>
                                    <TableCell>
                                        <Badge variant={o.is_active ? 'default' : 'secondary'}>
                                            {o.is_active ? 'active' : 'inactive'}
                                        </Badge>
                                    </TableCell>
                                </TableRow>
                            ))}
                            {outlets.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="py-8 text-center text-muted-foreground">
                                        No outlets yet. Create your first outlet to get started.
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

BrandOutletsIndex.layout = {
    breadcrumbs: [
        { title: 'Brand Dashboard', href: '/brand' },
        { title: 'Outlets', href: '/brand/outlets' },
    ],
};

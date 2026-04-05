import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';

type BinRow = {
    id: number;
    serial_number: string;
    outlet: string;
    status: string;
    fill_level: number;
    paired_at: string | null;
};

type Paginated = {
    data: BinRow[];
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
};

const statusVariant: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    active: 'default',
    unpaired: 'outline',
    maintenance: 'secondary',
    offline: 'destructive',
};

export default function BinsIndex({ bins }: { bins: Paginated }) {
    return (
        <>
            <Head title="Bins" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Bins</h1>
                    <Link
                        href="/admin/bins/create"
                        className="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                    >
                        Create Bin
                    </Link>
                </div>

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Serial</TableHead>
                                <TableHead>Outlet</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Fill Level</TableHead>
                                <TableHead>Paired</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {bins.data.map((bin) => (
                                <TableRow key={bin.id}>
                                    <TableCell className="font-mono text-sm">{bin.serial_number}</TableCell>
                                    <TableCell>{bin.outlet}</TableCell>
                                    <TableCell>
                                        <Badge variant={statusVariant[bin.status] ?? 'secondary'}>
                                            {bin.status}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{bin.fill_level}%</TableCell>
                                    <TableCell>{bin.paired_at ?? '-'}</TableCell>
                                </TableRow>
                            ))}
                            {bins.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="py-8 text-center text-muted-foreground">
                                        No bins yet.
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

BinsIndex.layout = {
    breadcrumbs: [
        { title: 'Admin Dashboard', href: '/admin' },
        { title: 'Bins', href: '/admin/bins' },
    ],
};

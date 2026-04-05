import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';

type Voucher = {
    id: number;
    name: string;
    brand: string;
    type: string;
    value: string;
    points_required: number;
    quota: number | null;
    claimed_count: number;
    claims_count: number;
    valid_from: string;
    valid_until: string;
    is_active: boolean;
};

type Paginated = {
    data: Voucher[];
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
};

export default function AdminVouchersIndex({ vouchers }: { vouchers: Paginated }) {
    return (
        <>
            <Head title="Vouchers" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">All Vouchers</h1>
                    <p className="text-sm text-muted-foreground">{vouchers.total} total</p>
                </div>

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Brand</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Value</TableHead>
                                <TableHead>Points</TableHead>
                                <TableHead>Quota</TableHead>
                                <TableHead>Claims</TableHead>
                                <TableHead>Valid</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {vouchers.data.map((v) => (
                                <TableRow key={v.id}>
                                    <TableCell className="font-medium">{v.name}</TableCell>
                                    <TableCell>{v.brand}</TableCell>
                                    <TableCell className="capitalize">{v.type.replace('_', ' ')}</TableCell>
                                    <TableCell>RM{parseFloat(v.value)}</TableCell>
                                    <TableCell>{v.points_required}</TableCell>
                                    <TableCell>
                                        {v.quota ? (
                                            <span>{v.claimed_count}/{v.quota}</span>
                                        ) : (
                                            <span className="text-muted-foreground">∞</span>
                                        )}
                                    </TableCell>
                                    <TableCell>{v.claims_count}</TableCell>
                                    <TableCell className="text-xs">{v.valid_from} → {v.valid_until}</TableCell>
                                    <TableCell>
                                        <Badge variant={v.is_active ? 'default' : 'secondary'}>
                                            {v.is_active ? 'active' : 'inactive'}
                                        </Badge>
                                    </TableCell>
                                </TableRow>
                            ))}
                            {vouchers.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={9} className="py-8 text-center text-muted-foreground">
                                        No vouchers created yet.
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

AdminVouchersIndex.layout = {
    breadcrumbs: [
        { title: 'Admin Dashboard', href: '/admin' },
        { title: 'Vouchers', href: '/admin/vouchers' },
    ],
};

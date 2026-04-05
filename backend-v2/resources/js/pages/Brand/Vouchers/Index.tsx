import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';

type Voucher = {
    id: number;
    name: string;
    type: string;
    value: string;
    points_required: number;
    valid_from: string;
    valid_until: string;
    is_active: boolean;
    claims_count: number;
};

type Paginated = {
    data: Voucher[];
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
};

const typeLabels: Record<string, string> = {
    discount: 'Discount',
    free_item: 'Free Item',
    cashback: 'Cashback',
};

export default function VouchersIndex({ vouchers, brandName }: { vouchers: Paginated; brandName: string }) {
    return (
        <>
            <Head title="Vouchers" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Vouchers</h1>
                        <p className="text-sm text-muted-foreground">{brandName}</p>
                    </div>
                    <Link
                        href="/brand/vouchers/create"
                        className="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                    >
                        Create Voucher
                    </Link>
                </div>

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Value</TableHead>
                                <TableHead>Points</TableHead>
                                <TableHead>Valid</TableHead>
                                <TableHead>Claims</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {vouchers.data.map((v) => (
                                <TableRow key={v.id}>
                                    <TableCell className="font-medium">{v.name}</TableCell>
                                    <TableCell>{typeLabels[v.type] ?? v.type}</TableCell>
                                    <TableCell>RM{parseFloat(v.value)}</TableCell>
                                    <TableCell>{v.points_required} pts</TableCell>
                                    <TableCell className="text-xs">{v.valid_from} → {v.valid_until}</TableCell>
                                    <TableCell>{v.claims_count}</TableCell>
                                    <TableCell>
                                        <Badge variant={v.is_active ? 'default' : 'secondary'}>
                                            {v.is_active ? 'active' : 'inactive'}
                                        </Badge>
                                    </TableCell>
                                </TableRow>
                            ))}
                            {vouchers.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={7} className="py-8 text-center text-muted-foreground">
                                        No vouchers yet. Create your first voucher to reward recyclers.
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

VouchersIndex.layout = {
    breadcrumbs: [
        { title: 'Brand Dashboard', href: '/brand' },
        { title: 'Vouchers', href: '/brand/vouchers' },
    ],
};

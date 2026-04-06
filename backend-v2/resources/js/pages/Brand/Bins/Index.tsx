import { Head } from '@inertiajs/react';
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
    has_pending_pickup: boolean;
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

function FillBar({ level }: { level: number }) {
    const color = level >= 80 ? 'bg-red-500' : level >= 50 ? 'bg-amber-400' : 'bg-emerald-500';
    return (
        <div className="flex items-center gap-2">
            <div className="h-2 w-24 overflow-hidden rounded-full bg-muted">
                <div className={`h-full rounded-full ${color}`} style={{ width: `${level}%` }} />
            </div>
            <span className="text-sm tabular-nums">{level}%</span>
        </div>
    );
}

export default function BrandBinsIndex({ bins, brandName }: { bins: Paginated; brandName?: string }) {
    return (
        <>
            <Head title="Bin Monitor" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div>
                    <h1 className="text-2xl font-bold">Bin Monitor</h1>
                    {brandName && <p className="text-sm text-muted-foreground">{brandName} — all outlets</p>}
                </div>

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Serial</TableHead>
                                <TableHead>Outlet</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Fill Level</TableHead>
                                <TableHead>Pickup Status</TableHead>
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
                                    <TableCell>
                                        <FillBar level={bin.fill_level} />
                                    </TableCell>
                                    <TableCell>
                                        {bin.has_pending_pickup ? (
                                            <Badge variant="secondary">Pending pickup</Badge>
                                        ) : (
                                            <span className="text-sm text-muted-foreground">—</span>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                            {bins.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="py-8 text-center text-muted-foreground">
                                        No bins found for your brand.
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

BrandBinsIndex.layout = {
    breadcrumbs: [
        { title: 'Brand Dashboard', href: '/brand' },
        { title: 'Bin Monitor', href: '/brand/bins' },
    ],
};

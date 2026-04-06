import { Head, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';

type DetectionRow = {
    id: number;
    waste_type: string;
    confidence: number;
    detected_brand: string | null;
    bin_serial: string | null;
    outlet: string | null;
    user: string;
    created_at: string;
};

type Paginated = {
    data: DetectionRow[];
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

const wasteColors: Record<string, string> = {
    paper_cup: 'bg-amber-100 text-amber-800',
    plastic_cup: 'bg-blue-100 text-blue-800',
    lid: 'bg-gray-100 text-gray-800',
    straw: 'bg-pink-100 text-pink-800',
    napkin: 'bg-orange-100 text-orange-800',
    liquid_waste: 'bg-cyan-100 text-cyan-800',
};

export default function BrandDetectionsIndex({
    events,
    filters,
    brandName,
}: {
    events: Paginated;
    filters: { waste_type?: string };
    brandName?: string;
}) {
    function filter(value: string) {
        router.get('/brand/detections', { waste_type: value || undefined }, { preserveState: true });
    }

    return (
        <>
            <Head title="Detection Events" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Detection Events</h1>
                        {brandName && <p className="text-sm text-muted-foreground">{brandName} — {events.total} detections</p>}
                    </div>
                    <select
                        className="rounded-lg border px-3 py-1.5 text-sm"
                        value={filters.waste_type ?? ''}
                        onChange={(e) => filter(e.target.value)}
                    >
                        <option value="">All waste types</option>
                        <option value="paper_cup">Paper Cup</option>
                        <option value="plastic_cup">Plastic Cup</option>
                        <option value="lid">Lid</option>
                        <option value="straw">Straw</option>
                        <option value="napkin">Napkin</option>
                        <option value="liquid_waste">Liquid Waste</option>
                    </select>
                </div>

                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Time</TableHead>
                                <TableHead>Waste Type</TableHead>
                                <TableHead>Conf.</TableHead>
                                <TableHead>Detected Brand</TableHead>
                                <TableHead>Bin</TableHead>
                                <TableHead>Outlet</TableHead>
                                <TableHead>User</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {events.data.map((e) => (
                                <TableRow key={e.id}>
                                    <TableCell className="text-xs text-muted-foreground">{e.created_at}</TableCell>
                                    <TableCell>
                                        <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${wasteColors[e.waste_type] ?? 'bg-gray-100 text-gray-800'}`}>
                                            {e.waste_type.replace('_', ' ')}
                                        </span>
                                    </TableCell>
                                    <TableCell className="tabular-nums text-sm">{e.confidence}%</TableCell>
                                    <TableCell>
                                        {e.detected_brand
                                            ? <Badge variant="default">{e.detected_brand}</Badge>
                                            : <span className="text-xs text-muted-foreground">—</span>
                                        }
                                    </TableCell>
                                    <TableCell className="font-mono text-xs">{e.bin_serial ?? '—'}</TableCell>
                                    <TableCell className="text-sm">{e.outlet ?? '—'}</TableCell>
                                    <TableCell className="text-sm">{e.user}</TableCell>
                                </TableRow>
                            ))}
                            {events.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={7} className="py-8 text-center text-muted-foreground">
                                        No detection events for your brand yet.
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

BrandDetectionsIndex.layout = {
    breadcrumbs: [
        { title: 'Brand Dashboard', href: '/brand' },
        { title: 'Detection Events', href: '/brand/detections' },
    ],
};

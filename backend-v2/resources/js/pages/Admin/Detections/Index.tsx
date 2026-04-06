import { Head, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';

type DetectionRow = {
    id: number;
    waste_type: string;
    confidence: number;
    input_method: string;
    detected_brand: string | null;
    bin_serial: string | null;
    outlet: string | null;
    brand: string | null;
    user: string;
    user_email: string | null;
    image_path: string | null;
    created_at: string;
};

type Paginated = {
    data: DetectionRow[];
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
    last_page: number;
};

const wasteColors: Record<string, string> = {
    paper_cup: 'bg-amber-100 text-amber-800',
    plastic_cup: 'bg-blue-100 text-blue-800',
    lid: 'bg-gray-100 text-gray-800',
    straw: 'bg-pink-100 text-pink-800',
    napkin: 'bg-orange-100 text-orange-800',
    liquid_waste: 'bg-cyan-100 text-cyan-800',
};

export default function AdminDetectionsIndex({
    events,
    filters,
}: {
    events: Paginated;
    filters: { waste_type?: string; brand_id?: string };
}) {
    function filter(key: string, value: string) {
        router.get('/admin/detections', { ...filters, [key]: value || undefined }, { preserveState: true });
    }

    return (
        <>
            <Head title="Detection Events" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Detection Events</h1>
                        <p className="text-sm text-muted-foreground">{events.total} total detections</p>
                    </div>
                    <div className="flex gap-2">
                        <select
                            className="rounded-lg border px-3 py-1.5 text-sm"
                            value={filters.waste_type ?? ''}
                            onChange={(e) => filter('waste_type', e.target.value)}
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
                                    <TableCell>
                                        <div className="text-sm">{e.user}</div>
                                        {e.user_email && <div className="text-xs text-muted-foreground">{e.user_email}</div>}
                                    </TableCell>
                                </TableRow>
                            ))}
                            {events.data.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={7} className="py-8 text-center text-muted-foreground">
                                        No detection events yet.
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

AdminDetectionsIndex.layout = {
    breadcrumbs: [
        { title: 'Admin Dashboard', href: '/admin' },
        { title: 'Detection Events', href: '/admin/detections' },
    ],
};

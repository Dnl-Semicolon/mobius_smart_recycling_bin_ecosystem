import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

type Route = {
    id: number;
    collector: string;
    status: string;
    stops_count: number;
    total_distance_km: string;
    total_duration_min: number;
    created_at: string;
};

type Paginated = { data: Route[]; total: number };

const statusVariant: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    pending: 'outline', accepted: 'secondary', in_progress: 'default', completed: 'default', rejected: 'destructive',
};

export default function RoutesIndex({ routes }: { routes: Paginated }) {
    return (
        <>
            <Head title="Routes" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Collection Routes</h1>
                    <Link href="/admin/routes/generate" className="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Generate Route
                    </Link>
                </div>
                <div className="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Collector</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Stops</TableHead>
                                <TableHead>Distance</TableHead>
                                <TableHead>Duration</TableHead>
                                <TableHead>Created</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {routes.data.map((r) => (
                                <TableRow key={r.id}>
                                    <TableCell className="font-mono">{r.id}</TableCell>
                                    <TableCell>{r.collector}</TableCell>
                                    <TableCell><Badge variant={statusVariant[r.status] ?? 'secondary'}>{r.status.replace(/_/g, ' ')}</Badge></TableCell>
                                    <TableCell>{r.stops_count}</TableCell>
                                    <TableCell>{r.total_distance_km} km</TableCell>
                                    <TableCell>{r.total_duration_min} min</TableCell>
                                    <TableCell>{r.created_at}</TableCell>
                                </TableRow>
                            ))}
                            {routes.data.length === 0 && (
                                <TableRow><TableCell colSpan={7} className="py-8 text-center text-muted-foreground">No routes yet.</TableCell></TableRow>
                            )}
                        </TableBody>
                    </Table>
                </div>
            </div>
        </>
    );
}

RoutesIndex.layout = {
    breadcrumbs: [{ title: 'Admin Dashboard', href: '/admin' }, { title: 'Routes', href: '/admin/routes' }],
};

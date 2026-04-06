import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Route = {
    id: number;
    status: string;
    stops_count: number;
    total_distance_km: string;
    total_duration_min: number;
    created_at: string;
};

const statusVariant: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    pending: 'outline', accepted: 'secondary', in_progress: 'default', completed: 'default',
};

export default function CollectorRoutesIndex({ routes }: { routes: Route[] }) {
    return (
        <>
            <Head title="My Routes" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">My Routes</h1>
                {routes.length > 0 ? (
                    <div className="space-y-3">
                        {routes.map((r) => (
                            <Link
                                key={r.id}
                                href={`/collector/routes/${r.id}`}
                                className="flex items-center justify-between rounded-xl border px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-900"
                            >
                                <div>
                                    <p className="font-medium">Route #{r.id}</p>
                                    <p className="text-sm text-muted-foreground">
                                        {r.stops_count} stops · {r.total_distance_km} km · {r.total_duration_min} min
                                    </p>
                                    <p className="text-xs text-muted-foreground">{r.created_at}</p>
                                </div>
                                <Badge variant={statusVariant[r.status] ?? 'secondary'}>
                                    {r.status.replace(/_/g, ' ')}
                                </Badge>
                            </Link>
                        ))}
                    </div>
                ) : (
                    <p className="text-sm text-muted-foreground">No routes assigned yet.</p>
                )}
            </div>
        </>
    );
}

CollectorRoutesIndex.layout = {
    breadcrumbs: [
        { title: 'Collector Dashboard', href: '/collector' },
        { title: 'Routes', href: '/collector/routes' },
    ],
};

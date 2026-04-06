import { Head, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { useEffect, useRef, useState } from 'react';

type Stop = {
    id: number;
    order: number;
    bin_serial: string;
    address: string;
    lat: number;
    lng: number;
    distance_km: string;
    duration_min: number;
    status: string;
};

type RouteData = {
    id: number;
    status: string;
    polyline: string;
    total_distance_km: string;
    total_duration_min: number;
    depot: { lat: number; lng: number };
    stops: Stop[];
};

export default function CollectorRouteShow({
    route,
    googleMapsKey,
}: {
    route: RouteData;
    googleMapsKey: string;
}) {
    const mapRef = useRef<HTMLDivElement>(null);
    const mapInstanceRef = useRef<google.maps.Map | null>(null);
    const userMarkerRef = useRef<google.maps.Marker | null>(null);
    const [userPos, setUserPos] = useState<{ lat: number; lng: number } | null>(null);
    const [mapLoaded, setMapLoaded] = useState(false);

    // Load Google Maps
    useEffect(() => {
        if (!googleMapsKey) return;

        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${googleMapsKey}&libraries=geometry`;
        script.async = true;
        script.onload = () => setMapLoaded(true);
        document.head.appendChild(script);
    }, [googleMapsKey]);

    // Initialize map
    useEffect(() => {
        if (!mapLoaded || !mapRef.current || !window.google?.maps) return;

        const map = new window.google.maps.Map(mapRef.current, {
            center: route.depot,
            zoom: 13,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true,
        });
        mapInstanceRef.current = map;

        // Draw route polyline
        if (route.polyline) {
            const path = window.google.maps.geometry.encoding.decodePath(route.polyline);
            new window.google.maps.Polyline({
                path,
                geodesic: true,
                strokeColor: '#059669',
                strokeOpacity: 0.8,
                strokeWeight: 5,
                map,
            });
        }

        // Depot marker
        new window.google.maps.Marker({
            position: route.depot,
            map,
            label: 'S',
            title: 'Start/End',
        });

        // Stop markers
        route.stops.forEach((stop) => {
            const marker = new window.google.maps.Marker({
                position: { lat: stop.lat, lng: stop.lng },
                map,
                label: String(stop.order),
                title: `${stop.bin_serial} — ${stop.address}`,
                icon: stop.status === 'completed'
                    ? { url: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png' }
                    : undefined,
            });

            const infoWindow = new window.google.maps.InfoWindow({
                content: `<div style="font-size:13px"><strong>Stop ${stop.order}: ${stop.bin_serial}</strong><br>${stop.address}<br>${stop.distance_km} km · ${stop.duration_min} min</div>`,
            });
            marker.addListener('click', () => infoWindow.open(map, marker));
        });

        // Fit bounds to show all stops
        const bounds = new window.google.maps.LatLngBounds();
        bounds.extend(route.depot);
        route.stops.forEach((s) => bounds.extend({ lat: s.lat, lng: s.lng }));
        map.fitBounds(bounds, 50);
    }, [mapLoaded]);

    // GPS tracking
    useEffect(() => {
        if (!navigator.geolocation) return;

        const watchId = navigator.geolocation.watchPosition(
            (pos) => {
                const loc = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                setUserPos(loc);

                if (mapInstanceRef.current) {
                    if (!userMarkerRef.current) {
                        userMarkerRef.current = new window.google.maps.Marker({
                            position: loc,
                            map: mapInstanceRef.current,
                            icon: {
                                path: window.google.maps.SymbolPath.CIRCLE,
                                scale: 10,
                                fillColor: '#3B82F6',
                                fillOpacity: 1,
                                strokeColor: '#fff',
                                strokeWeight: 3,
                            },
                            title: 'Your Location',
                            zIndex: 999,
                        });
                    } else {
                        userMarkerRef.current.setPosition(loc);
                    }
                }
            },
            () => {},
            { enableHighAccuracy: true, maximumAge: 5000 }
        );

        return () => navigator.geolocation.clearWatch(watchId);
    }, [mapLoaded]);

    function recenter() {
        if (userPos && mapInstanceRef.current) {
            mapInstanceRef.current.panTo(userPos);
            mapInstanceRef.current.setZoom(15);
        }
    }

    function handleAccept() {
        router.post(`/collector/routes/${route.id}/accept`);
    }

    function handleStart() {
        router.post(`/collector/routes/${route.id}/start`);
    }

    function handleCompleteStop(order: number) {
        router.post(`/collector/routes/${route.id}/stops/${order}/complete`, {
            lat: userPos?.lat,
            lng: userPos?.lng,
        });
    }

    const nextStop = route.stops.find((s) => s.status === 'pending');

    return (
        <>
            <Head title={`Route #${route.id}`} />
            <div className="flex h-full flex-1 flex-col">
                {/* Map */}
                <div ref={mapRef} className="h-[50vh] min-h-[300px] w-full bg-gray-100" />

                {/* Controls overlay */}
                <div className="absolute top-20 right-4 z-10 flex flex-col gap-2">
                    <button
                        onClick={recenter}
                        className="rounded-lg bg-white px-3 py-2 text-sm font-semibold shadow-md hover:bg-gray-50"
                    >
                        Re-center
                    </button>
                </div>

                {/* Route info + stops */}
                <div className="flex-1 overflow-auto p-4">
                    <div className="flex items-center justify-between mb-4">
                        <div>
                            <h1 className="text-xl font-bold">Route #{route.id}</h1>
                            <p className="text-sm text-muted-foreground">
                                {route.total_distance_km} km · {route.total_duration_min} min · {route.stops.length} stops
                            </p>
                        </div>
                        <Badge variant={route.status === 'in_progress' ? 'default' : 'outline'}>
                            {route.status.replace(/_/g, ' ')}
                        </Badge>
                    </div>

                    {/* Action buttons */}
                    {route.status === 'pending' && (
                        <button onClick={handleAccept} className="mb-4 w-full rounded-lg bg-emerald-600 py-3 text-sm font-semibold text-white hover:bg-emerald-700">
                            Accept Route
                        </button>
                    )}
                    {route.status === 'accepted' && (
                        <button onClick={handleStart} className="mb-4 w-full rounded-lg bg-emerald-600 py-3 text-sm font-semibold text-white hover:bg-emerald-700">
                            Start Navigation
                        </button>
                    )}

                    {/* Next stop highlight */}
                    {nextStop && route.status === 'in_progress' && (
                        <div className="mb-4 rounded-xl border-2 border-emerald-500 bg-emerald-50 p-4 dark:bg-emerald-950">
                            <p className="text-xs font-semibold uppercase text-emerald-700 dark:text-emerald-400">Next Stop</p>
                            <p className="text-lg font-bold">Stop {nextStop.order}: {nextStop.bin_serial}</p>
                            <p className="text-sm text-muted-foreground">{nextStop.address}</p>
                            <p className="text-sm text-muted-foreground">{nextStop.distance_km} km · {nextStop.duration_min} min</p>
                            <button
                                onClick={() => handleCompleteStop(nextStop.order)}
                                className="mt-3 w-full rounded-lg bg-emerald-600 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                            >
                                Mark Complete
                            </button>
                        </div>
                    )}

                    {/* All stops */}
                    <div className="space-y-2">
                        {route.stops.map((s) => (
                            <div key={s.id} className={`flex items-center justify-between rounded-lg border px-4 py-3 ${s.status === 'completed' ? 'bg-gray-50 opacity-60' : ''}`}>
                                <div>
                                    <p className="text-sm font-medium">Stop {s.order}: {s.bin_serial}</p>
                                    <p className="text-xs text-muted-foreground">{s.address || `${s.lat}, ${s.lng}`}</p>
                                </div>
                                <Badge variant={s.status === 'completed' ? 'default' : 'outline'}>
                                    {s.status}
                                </Badge>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </>
    );
}

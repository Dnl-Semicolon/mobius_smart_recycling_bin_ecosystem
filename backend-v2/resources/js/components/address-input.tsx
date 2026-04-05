import { useEffect, useRef, useState } from 'react';

const GOOGLE_MAPS_API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

let scriptLoaded = false;
let scriptLoading = false;
const onLoadCallbacks: (() => void)[] = [];

function loadGoogleMaps(): Promise<boolean> {
    if (scriptLoaded && window.google?.maps?.places?.Autocomplete) {
        return Promise.resolve(true);
    }

    return new Promise((resolve) => {
        if (scriptLoading) {
            onLoadCallbacks.push(() => resolve(!!window.google?.maps?.places?.Autocomplete));
            return;
        }

        scriptLoading = true;
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_MAPS_API_KEY}&libraries=places`;
        script.async = true;
        script.onload = () => {
            scriptLoaded = true;
            scriptLoading = false;
            const ok = !!window.google?.maps?.places?.Autocomplete;
            resolve(ok);
            onLoadCallbacks.forEach((cb) => cb());
            onLoadCallbacks.length = 0;
        };
        script.onerror = () => {
            scriptLoading = false;
            resolve(false);
        };
        document.head.appendChild(script);
    });
}

export type PlaceResult = {
    name: string;
    address: string;
    street: string;
    city: string;
    state: string;
    postcode: string;
    country: string;
    lat: number;
    lng: number;
};

function parseAddressComponents(components: google.maps.GeocoderAddressComponent[]): {
    street: string; city: string; state: string; postcode: string; country: string;
} {
    const get = (type: string) => components.find((c) => c.types.includes(type))?.long_name ?? '';
    const streetNum = get('street_number');
    const route = get('route');

    return {
        street: [streetNum, route].filter(Boolean).join(' '),
        city: get('locality') || get('sublocality_level_1') || get('administrative_area_level_2'),
        state: get('administrative_area_level_1'),
        postcode: get('postal_code'),
        country: get('country'),
    };
}

type Status = 'loading' | 'ready' | 'retrying' | 'offline';

export default function AddressInput({
    onPlaceSelect,
}: {
    onPlaceSelect: (place: PlaceResult) => void;
}) {
    const inputRef = useRef<HTMLInputElement>(null);
    const onPlaceSelectRef = useRef(onPlaceSelect);
    onPlaceSelectRef.current = onPlaceSelect;

    const [status, setStatus] = useState<Status>('loading');
    const [retryCount, setRetryCount] = useState(0);
    const mountedRef = useRef(false);

    useEffect(() => {
        if (!GOOGLE_MAPS_API_KEY) {
            setStatus('offline');
            return;
        }

        let cancelled = false;

        async function attempt() {
            const ok = await loadGoogleMaps();

            if (cancelled) return;

            if (ok && inputRef.current && !mountedRef.current) {
                const ac = new window.google.maps.places.Autocomplete(inputRef.current, {
                    componentRestrictions: { country: 'my' },
                    fields: ['formatted_address', 'geometry', 'name', 'address_components'],
                });

                ac.addListener('place_changed', () => {
                    const p = ac.getPlace();
                    if (!p.geometry?.location) return;

                    const parts = parseAddressComponents(p.address_components ?? []);

                    onPlaceSelectRef.current({
                        name: p.name ?? '',
                        address: p.formatted_address ?? '',
                        street: parts.street,
                        city: parts.city,
                        state: parts.state,
                        postcode: parts.postcode,
                        country: parts.country,
                        lat: p.geometry.location.lat(),
                        lng: p.geometry.location.lng(),
                    });
                });

                mountedRef.current = true;
                setStatus('ready');
            } else if (!ok && retryCount < 5) {
                setStatus('retrying');
                setTimeout(() => {
                    if (!cancelled) {
                        setRetryCount((c) => c + 1);
                        attempt();
                    }
                }, 2000);
            } else if (!ok) {
                setStatus('offline');
            }
        }

        attempt();

        // Background recovery: if offline, keep checking
        const interval = setInterval(() => {
            if (mountedRef.current) {
                clearInterval(interval);
                return;
            }
            if (window.google?.maps?.places?.Autocomplete && inputRef.current) {
                attempt();
            }
        }, 5000);

        return () => {
            cancelled = true;
            clearInterval(interval);
        };
    }, []);

    return (
        <div>
            <div className="flex items-center gap-2 mb-1">
                <span className="text-sm font-medium">Address</span>
                {status === 'loading' && <span className="text-xs text-blue-500">Loading...</span>}
                {status === 'retrying' && <span className="text-xs text-amber-500">Retrying ({retryCount}/5)...</span>}
                {status === 'ready' && <span className="text-xs text-emerald-500">Google Places connected</span>}
                {status === 'offline' && <span className="text-xs text-red-500">Offline — manual entry</span>}
            </div>
            <input
                ref={inputRef}
                type="text"
                className="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                placeholder={status === 'ready' ? 'Search for an address...' : status === 'offline' ? 'Enter address manually' : 'Loading Google Places...'}
            />
        </div>
    );
}

import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AddressInput, { type PlaceResult } from '@/components/address-input';

export default function PlacesTest() {
    const [fields, setFields] = useState<PlaceResult | null>(null);

    return (
        <>
            <Head title="Places Test" />
            <div style={{ maxWidth: 600, margin: '40px auto', padding: 20, fontFamily: 'system-ui' }}>
                <h1 style={{ fontSize: 20, fontWeight: 'bold', marginBottom: 20 }}>Google Places Test</h1>

                <AddressInput onPlaceSelect={(place) => setFields(place)} />

                {fields && (
                    <div style={{ marginTop: 20, display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                        {Object.entries(fields).map(([key, val]) => (
                            <div key={key}>
                                <label style={{ fontWeight: 600, fontSize: 13, textTransform: 'capitalize' }}>{key}:</label>
                                <input
                                    value={String(val)}
                                    readOnly
                                    style={{ width: '100%', padding: '6px 10px', border: '1px solid #86efac', borderRadius: 6, background: '#f0fdf4', fontSize: 13 }}
                                />
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

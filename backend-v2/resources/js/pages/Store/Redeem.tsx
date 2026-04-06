import { Head, router, usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { useState, useEffect, useRef } from 'react';
import { Html5Qrcode } from 'html5-qrcode';

type Claim = {
    id: number;
    qr_code: string;
    voucher_name: string;
    voucher_value: string;
    voucher_type: string;
    brand: string;
    user_name: string;
    status: string;
    claimed_at: string;
    redeemed_at: string | null;
};

const statusVariant: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    claimed: 'outline',
    redeemed: 'default',
    expired: 'destructive',
};

export default function StoreRedeem({
    claim,
    error,
}: {
    claim?: Claim;
    error?: string;
}) {
    const { flash } = usePage<{ flash: { success?: string } }>().props;
    const [qrCode, setQrCode] = useState('');
    const [scanning, setScanning] = useState(false);
    const scannerRef = useRef<Html5Qrcode | null>(null);
    const scannerContainerId = 'qr-scanner';

    async function startScanner() {
        setScanning(true);

        try {
            const scanner = new Html5Qrcode(scannerContainerId);
            scannerRef.current = scanner;

            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => {
                    // QR detected — stop scanner and look up
                    scanner.stop().then(() => {
                        scannerRef.current = null;
                        setScanning(false);
                        setQrCode(decodedText);
                        router.post('/store/redeem/lookup', { qr_code: decodedText });
                    });
                },
                () => {
                    // Scan error (no QR in frame) — ignore
                },
            );
        } catch {
            setScanning(false);
        }
    }

    function stopScanner() {
        if (scannerRef.current) {
            scannerRef.current.stop().then(() => {
                scannerRef.current = null;
                setScanning(false);
            });
        }
    }

    // Cleanup on unmount
    useEffect(() => {
        return () => {
            if (scannerRef.current) {
                scannerRef.current.stop().catch(() => {});
            }
        };
    }, []);

    function handleManualLookup(e: React.FormEvent) {
        e.preventDefault();
        router.post('/store/redeem/lookup', { qr_code: qrCode });
    }

    function handleRedeem(claimId: number) {
        if (confirm('Mark this voucher as redeemed?')) {
            router.post('/store/redeem/confirm', { claim_id: claimId });
        }
    }

    return (
        <>
            <Head title="Redeem Voucher" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div>
                    <h1 className="text-2xl font-bold">Redeem Voucher</h1>
                    <p className="text-sm text-muted-foreground">Scan a customer's QR code or enter it manually.</p>
                </div>

                {flash?.success && (
                    <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-400">
                        {flash.success}
                    </div>
                )}

                {/* QR Scanner */}
                <div className="max-w-md">
                    <div id={scannerContainerId} className={`mb-3 overflow-hidden rounded-lg ${scanning ? '' : 'hidden'}`} />
                    {scanning ? (
                        <button
                            onClick={stopScanner}
                            className="w-full rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-50"
                        >
                            Close Scanner
                        </button>
                    ) : (
                        <button
                            onClick={startScanner}
                            className="w-full rounded-lg bg-gray-900 px-4 py-3 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                        >
                            Open Camera Scanner
                        </button>
                    )}
                </div>

                {/* Manual entry */}
                <div>
                    <p className="mb-2 text-xs text-muted-foreground">Or enter code manually:</p>
                    <form onSubmit={handleManualLookup} className="flex max-w-md gap-2">
                        <input
                            type="text"
                            value={qrCode}
                            onChange={(e) => setQrCode(e.target.value.toUpperCase())}
                            className="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            placeholder="VCR-XXXXXXXXXXXX"
                        />
                        <button
                            type="submit"
                            className="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                        >
                            Look Up
                        </button>
                    </form>
                </div>

                {error && (
                    <div className="max-w-md rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-400">
                        {error}
                    </div>
                )}

                {claim && (
                    <div className="max-w-md rounded-xl border p-6">
                        <div className="flex items-center justify-between mb-4">
                            <h2 className="text-lg font-semibold">{claim.voucher_name}</h2>
                            <Badge variant={statusVariant[claim.status] ?? 'secondary'}>
                                {claim.status}
                            </Badge>
                        </div>
                        <dl className="space-y-2 text-sm">
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">Brand</dt>
                                <dd className="font-medium">{claim.brand}</dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">Value</dt>
                                <dd className="font-medium text-emerald-600">RM{parseFloat(claim.voucher_value)}</dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">Customer</dt>
                                <dd className="font-medium">{claim.user_name}</dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">QR Code</dt>
                                <dd className="font-mono text-xs">{claim.qr_code}</dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">Claimed</dt>
                                <dd>{claim.claimed_at}</dd>
                            </div>
                            {claim.redeemed_at && (
                                <div className="flex justify-between">
                                    <dt className="text-muted-foreground">Redeemed</dt>
                                    <dd>{claim.redeemed_at}</dd>
                                </div>
                            )}
                        </dl>

                        {claim.status === 'claimed' && (
                            <button
                                onClick={() => handleRedeem(claim.id)}
                                className="mt-4 w-full rounded-lg bg-emerald-600 py-3 text-sm font-semibold text-white hover:bg-emerald-700"
                            >
                                Confirm Redemption
                            </button>
                        )}

                        {claim.status === 'redeemed' && (
                            <p className="mt-4 text-center text-sm text-emerald-600 font-medium">This voucher has been redeemed.</p>
                        )}
                    </div>
                )}
            </div>
        </>
    );
}

StoreRedeem.layout = {
    breadcrumbs: [
        { title: 'Store Dashboard', href: '/store' },
        { title: 'Redeem', href: '/store/redeem' },
    ],
};

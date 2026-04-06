import { Head, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { QRCodeSVG } from 'qrcode.react';
import { useState, useRef, useCallback } from 'react';

type Voucher = {
    id: number;
    name: string;
    description: string | null;
    brand: string;
    type: string;
    value: string;
    points_required: number;
    valid_until: string;
    can_afford: boolean;
    is_promo: boolean;
    remaining: number | null;
    sold_out: boolean;
};

type Claim = {
    id: number;
    voucher_name: string;
    voucher_value: string;
    points_spent: number;
    status: string;
    qr_code: string;
    claimed_at: string;
    redeemed_at: string | null;
};

const typeLabels: Record<string, string> = {
    discount: 'Discount',
    free_item: 'Free Item',
    cashback: 'Cashback',
};

const statusVariant: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    claimed: 'outline',
    redeemed: 'default',
    expired: 'destructive',
};

export default function PublicVouchers({
    vouchers,
    myClaims,
    pointsBalance,
}: {
    vouchers: Voucher[];
    myClaims: Claim[];
    pointsBalance: number;
}) {
    const [qrModal, setQrModal] = useState<{ code: string; name: string } | null>(null);
    const qrRef = useRef<HTMLDivElement>(null);

    function downloadQr() {
        if (!qrRef.current) return;
        const svg = qrRef.current.querySelector('svg');
        if (!svg) return;
        const canvas = document.createElement('canvas');
        canvas.width = 512;
        canvas.height = 512;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
        const data = new XMLSerializer().serializeToString(svg);
        const img = new Image();
        img.onload = () => {
            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, 512, 512);
            ctx.drawImage(img, 0, 0, 512, 512);
            const a = document.createElement('a');
            a.download = `voucher-${qrModal?.code ?? 'qr'}.png`;
            a.href = canvas.toDataURL('image/png');
            a.click();
        };
        img.src = 'data:image/svg+xml;base64,' + btoa(data);
    }

    function handleClaim(id: number, name: string, points: number) {
        if (confirm(`Claim "${name}" for ${points} points? This will deduct from your balance.`)) {
            router.post(`/public/vouchers/${id}/claim`);
        }
    }

    return (
        <>
            <Head title="Vouchers" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Vouchers</h1>
                        <p className="text-sm text-muted-foreground">Browse and claim rewards with your recycling points.</p>
                    </div>
                    <div className="rounded-lg border px-4 py-2 text-sm">
                        Your Points: <span className="font-bold text-emerald-600">{pointsBalance}</span>
                    </div>
                </div>

                {/* Available Vouchers */}
                <div>
                    <h2 className="mb-3 text-lg font-semibold">Available Vouchers</h2>
                    {vouchers.length > 0 ? (
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            {vouchers.map((v) => (
                                <div key={v.id} className="flex flex-col rounded-xl border p-5">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <p className="text-xs text-muted-foreground">{v.brand}</p>
                                            <h3 className="text-lg font-semibold">{v.name}</h3>
                                        </div>
                                        <div className="flex gap-1">
                                            {v.is_promo && (
                                                <Badge variant={v.sold_out ? 'destructive' : 'default'}>
                                                    {v.sold_out ? 'Sold out' : `${v.remaining} left`}
                                                </Badge>
                                            )}
                                            <Badge variant="secondary">{typeLabels[v.type] ?? v.type}</Badge>
                                        </div>
                                    </div>
                                    {v.description && (
                                        <p className="mt-1 text-sm text-muted-foreground">{v.description}</p>
                                    )}
                                    <div className="mt-3 flex items-baseline gap-1">
                                        <span className="text-2xl font-bold text-emerald-600">RM{parseFloat(v.value)}</span>
                                        <span className="text-sm text-muted-foreground">off</span>
                                    </div>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        {v.points_required} points required · Valid until {v.valid_until}
                                    </p>
                                    <button
                                        onClick={() => handleClaim(v.id, v.name, v.points_required)}
                                        disabled={!v.can_afford || v.sold_out}
                                        className={`mt-4 w-full rounded-lg py-2 text-sm font-semibold ${
                                            v.sold_out
                                                ? 'border text-muted-foreground cursor-not-allowed'
                                                : v.can_afford
                                                    ? 'bg-emerald-600 text-white hover:bg-emerald-700'
                                                    : 'border text-muted-foreground cursor-not-allowed'
                                        }`}
                                    >
                                        {v.sold_out ? 'Sold Out' : v.can_afford ? `Claim for ${v.points_required} pts` : `Need ${v.points_required - pointsBalance} more pts`}
                                    </button>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="text-sm text-muted-foreground">No vouchers available right now.</p>
                    )}
                </div>

                {/* My Claims */}
                {myClaims.length > 0 && (
                    <div>
                        <h2 className="mb-3 text-lg font-semibold">My Claimed Vouchers</h2>
                        <div className="grid gap-4 md:grid-cols-2">
                            {myClaims.map((c) => (
                                <div key={c.id} className="flex items-center gap-4 rounded-xl border p-4">
                                    <button
                                        onClick={() => c.status === 'claimed' ? setQrModal({ code: c.qr_code, name: c.voucher_name }) : null}
                                        className={`flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-white p-1 ${c.status === 'claimed' ? 'cursor-pointer hover:ring-2 hover:ring-emerald-500' : ''}`}
                                    >
                                        {c.status === 'claimed' ? (
                                            <QRCodeSVG value={c.qr_code} size={56} />
                                        ) : (
                                            <span className="text-xs text-muted-foreground">{c.status}</span>
                                        )}
                                    </button>
                                    <div className="flex-1">
                                        <p className="font-medium">{c.voucher_name}</p>
                                        <p className="text-sm text-muted-foreground">
                                            RM{parseFloat(c.voucher_value)} · {c.points_spent} pts spent
                                        </p>
                                        <p className="text-xs text-muted-foreground">{c.claimed_at}</p>
                                    </div>
                                    <Badge variant={statusVariant[c.status] ?? 'secondary'}>
                                        {c.status}
                                    </Badge>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
                {/* QR Modal */}
                {qrModal && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50" onClick={() => setQrModal(null)}>
                        <div className="w-full max-w-sm rounded-xl bg-white p-6 text-center shadow-xl dark:bg-gray-900" onClick={(e) => e.stopPropagation()}>
                            <div className="flex items-center justify-between mb-4">
                                <h2 className="text-lg font-bold">Voucher QR Code</h2>
                                <button onClick={() => setQrModal(null)} className="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                            </div>
                            <p className="text-sm text-muted-foreground mb-4">{qrModal.name}</p>
                            <div ref={qrRef} className="flex justify-center mb-4">
                                <QRCodeSVG value={qrModal.code} size={256} level="H" />
                            </div>
                            <p className="font-mono text-sm text-muted-foreground mb-4">{qrModal.code}</p>
                            <div className="flex gap-3 justify-center">
                                <button
                                    onClick={downloadQr}
                                    className="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                                >
                                    Download QR
                                </button>
                                <button
                                    onClick={() => setQrModal(null)}
                                    className="rounded-lg border px-4 py-2 text-sm font-semibold"
                                >
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}

PublicVouchers.layout = {
    breadcrumbs: [
        { title: 'My Dashboard', href: '/public' },
        { title: 'Vouchers', href: '/public/vouchers' },
    ],
};

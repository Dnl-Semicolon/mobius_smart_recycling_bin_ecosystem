import { Head, router } from '@inertiajs/react';
import { QRCodeSVG } from 'qrcode.react';
import { useRef, useState } from 'react';
import { Badge } from '@/components/ui/badge';

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

export default function PublicVouchers({
    vouchers,
    myClaims,
    pointsBalance,
}: {
    vouchers: Voucher[];
    myClaims: Claim[];
    pointsBalance: number;
}) {
    const [tab, setTab] = useState<'available' | 'claimed'>('available');
    const [qrModal, setQrModal] = useState<{ code: string; name: string } | null>(null);
    const qrRef = useRef<HTMLDivElement>(null);

    function handleClaim(id: number, name: string, points: number) {
        if (confirm(`Claim "${name}" for ${points} points?`)) {
            router.post(`/public/vouchers/${id}/claim`);
        }
    }

    function downloadQr() {
        if (!qrRef.current) return;
        const svg = qrRef.current.querySelector('svg');
        if (!svg) return;
        const canvas = document.createElement('canvas');
        canvas.width = 512; canvas.height = 512;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
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
        img.src = 'data:image/svg+xml;base64,' + btoa(new XMLSerializer().serializeToString(svg));
    }

    return (
        <>
            <Head title="Vouchers" />
            <div className="flex flex-col gap-4 p-4">

                {/* Balance pill */}
                <div className="flex items-center justify-between rounded-2xl bg-emerald-600 px-4 py-3 text-white">
                    <p className="text-sm font-medium">Your points</p>
                    <p className="text-2xl font-bold">{pointsBalance}</p>
                </div>

                {/* Tabs */}
                <div className="flex rounded-xl border p-1 bg-muted">
                    {(['available', 'claimed'] as const).map((t) => (
                        <button
                            key={t}
                            onClick={() => setTab(t)}
                            className={`flex-1 rounded-lg py-1.5 text-sm font-medium transition-colors ${
                                tab === t
                                    ? 'bg-background shadow-sm text-foreground'
                                    : 'text-muted-foreground'
                            }`}
                        >
                            {t === 'available' ? 'Available' : `My Vouchers (${myClaims.length})`}
                        </button>
                    ))}
                </div>

                {/* Available vouchers */}
                {tab === 'available' && (
                    <div className="flex flex-col gap-3">
                        {vouchers.length === 0 && (
                            <p className="py-8 text-center text-sm text-muted-foreground">No vouchers available right now.</p>
                        )}
                        {vouchers.map((v) => (
                            <div key={v.id} className="rounded-2xl border bg-card p-4 shadow-sm">
                                <div className="flex items-start justify-between gap-2">
                                    <div className="flex-1">
                                        <p className="text-xs text-muted-foreground">{v.brand}</p>
                                        <p className="font-semibold leading-tight">{v.name}</p>
                                        {v.description && <p className="mt-0.5 text-xs text-muted-foreground">{v.description}</p>}
                                    </div>
                                    <div className="flex flex-col items-end gap-1">
                                        <Badge variant="secondary">{typeLabels[v.type] ?? v.type}</Badge>
                                        {v.is_promo && (
                                            <Badge variant={v.sold_out ? 'destructive' : 'outline'}>
                                                {v.sold_out ? 'Sold out' : `${v.remaining} left`}
                                            </Badge>
                                        )}
                                    </div>
                                </div>

                                <div className="mt-3 flex items-end justify-between">
                                    <div>
                                        <p className="text-2xl font-bold text-emerald-600">RM{parseFloat(v.value)}</p>
                                        <p className="text-xs text-muted-foreground">Valid until {v.valid_until}</p>
                                    </div>
                                    <button
                                        onClick={() => handleClaim(v.id, v.name, v.points_required)}
                                        disabled={!v.can_afford || v.sold_out}
                                        className={`rounded-xl px-4 py-2 text-sm font-semibold transition-colors ${
                                            v.sold_out
                                                ? 'cursor-not-allowed bg-muted text-muted-foreground'
                                                : v.can_afford
                                                    ? 'bg-emerald-600 text-white active:bg-emerald-700'
                                                    : 'cursor-not-allowed bg-muted text-muted-foreground'
                                        }`}
                                    >
                                        {v.sold_out
                                            ? 'Sold Out'
                                            : v.can_afford
                                                ? `${v.points_required} pts`
                                                : `Need ${v.points_required - pointsBalance} more`}
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {/* My claimed vouchers */}
                {tab === 'claimed' && (
                    <div className="flex flex-col gap-3">
                        {myClaims.length === 0 && (
                            <p className="py-8 text-center text-sm text-muted-foreground">No claimed vouchers yet.</p>
                        )}
                        {myClaims.map((c) => (
                            <div key={c.id} className="flex items-center gap-4 rounded-2xl border bg-card p-4 shadow-sm">
                                <button
                                    onClick={() => c.status === 'claimed' ? setQrModal({ code: c.qr_code, name: c.voucher_name }) : undefined}
                                    className="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-white p-1.5 shadow-sm"
                                >
                                    {c.status === 'claimed'
                                        ? <QRCodeSVG value={c.qr_code} size={52} />
                                        : <span className="text-xs text-muted-foreground">{c.status}</span>
                                    }
                                </button>
                                <div className="flex-1 min-w-0">
                                    <p className="truncate font-medium">{c.voucher_name}</p>
                                    <p className="text-sm text-muted-foreground">RM{parseFloat(c.voucher_value)} · {c.points_spent} pts</p>
                                    <p className="text-xs text-muted-foreground">{c.claimed_at}</p>
                                </div>
                                <Badge variant={c.status === 'redeemed' ? 'default' : c.status === 'expired' ? 'destructive' : 'outline'}>
                                    {c.status}
                                </Badge>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* QR full-screen modal */}
            {qrModal && (
                <div className="fixed inset-0 z-50 flex flex-col items-center justify-center bg-black/80 p-6" onClick={() => setQrModal(null)}>
                    <div className="w-full max-w-xs rounded-3xl bg-white p-6 text-center shadow-2xl" onClick={(e) => e.stopPropagation()}>
                        <p className="mb-1 text-xs text-gray-500">{qrModal.name}</p>
                        <p className="mb-4 text-sm font-bold text-gray-800">Show at the store to redeem</p>
                        <div ref={qrRef} className="flex justify-center">
                            <QRCodeSVG value={qrModal.code} size={220} level="H" />
                        </div>
                        <p className="mt-3 font-mono text-xs text-gray-400">{qrModal.code}</p>
                        <div className="mt-4 flex gap-2">
                            <button
                                onClick={downloadQr}
                                className="flex-1 rounded-xl bg-emerald-600 py-2.5 text-sm font-semibold text-white"
                            >
                                Save QR
                            </button>
                            <button
                                onClick={() => setQrModal(null)}
                                className="flex-1 rounded-xl border py-2.5 text-sm font-semibold"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}

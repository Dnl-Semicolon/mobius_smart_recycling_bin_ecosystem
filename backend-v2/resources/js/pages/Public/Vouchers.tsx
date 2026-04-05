import { Head, router } from '@inertiajs/react';
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
                                        <Badge variant="secondary">{typeLabels[v.type] ?? v.type}</Badge>
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
                                        disabled={!v.can_afford}
                                        className={`mt-4 w-full rounded-lg py-2 text-sm font-semibold ${
                                            v.can_afford
                                                ? 'bg-emerald-600 text-white hover:bg-emerald-700'
                                                : 'border text-muted-foreground cursor-not-allowed'
                                        }`}
                                    >
                                        {v.can_afford ? `Claim for ${v.points_required} pts` : `Need ${v.points_required - pointsBalance} more pts`}
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
                                    <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-center text-xs font-mono dark:bg-gray-800">
                                        {c.status === 'claimed' ? (
                                            <span className="text-[8px] leading-tight break-all">{c.qr_code}</span>
                                        ) : (
                                            <span className="text-muted-foreground">{c.status}</span>
                                        )}
                                    </div>
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

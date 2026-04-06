import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';

type Stats = {
    points_balance: number;
    current_streak: number;
    longest_streak: number;
    claimed_vouchers: number;
};

type Transaction = {
    type: string;
    points: number;
    description: string | null;
    date: string;
};

export default function PublicDashboard({
    stats,
    recentTransactions,
}: {
    stats: Stats;
    recentTransactions: Transaction[];
}) {
    return (
        <>
            <Head title="My Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-bold">My Dashboard</h1>
                <p className="text-muted-foreground">Your recycling stats and rewards.</p>
                <div className="grid auto-rows-min gap-4 md:grid-cols-4">
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Points Balance</p>
                        <p className="text-3xl font-bold text-emerald-600">{stats.points_balance}</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Current Streak</p>
                        <p className="text-3xl font-bold">{stats.current_streak}</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Longest Streak</p>
                        <p className="text-3xl font-bold">{stats.longest_streak}</p>
                    </div>
                    <div className="rounded-xl border p-6">
                        <p className="text-sm text-muted-foreground">Vouchers Claimed</p>
                        <p className="text-3xl font-bold">{stats.claimed_vouchers}</p>
                    </div>
                </div>

                {recentTransactions.length > 0 && (
                    <div>
                        <h2 className="mb-3 text-lg font-semibold">Recent Activity</h2>
                        <div className="space-y-2">
                            {recentTransactions.map((t, i) => (
                                <div key={i} className="flex items-center justify-between rounded-lg border px-4 py-3 text-sm">
                                    <div>
                                        <p className="font-medium">{t.description ?? t.type}</p>
                                        <p className="text-xs text-muted-foreground">{t.date}</p>
                                    </div>
                                    <Badge variant={t.points > 0 ? 'default' : 'secondary'}>
                                        {t.points > 0 ? '+' : ''}{t.points} pts
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

PublicDashboard.layout = {
    breadcrumbs: [{ title: 'My Dashboard', href: '/public' }],
};

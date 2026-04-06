import { Head } from '@inertiajs/react';
import { Flame, Trophy } from 'lucide-react';

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

const typeColor: Record<string, string> = {
    earned: 'text-emerald-600',
    bonus: 'text-blue-600',
    spent: 'text-red-500',
    expired: 'text-gray-400',
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
            <Head title="Home" />
            <div className="flex flex-col gap-4 p-4">

                {/* Points hero */}
                <div className="rounded-2xl bg-emerald-600 p-5 text-white shadow-md">
                    <p className="text-sm font-medium opacity-80">Total Points</p>
                    <p className="mt-1 text-5xl font-bold tracking-tight">{stats.points_balance}</p>
                    <p className="mt-1 text-xs opacity-70">Redeem for vouchers on the Vouchers tab</p>
                </div>

                {/* Streak + vouchers */}
                <div className="grid grid-cols-2 gap-3">
                    <div className="flex items-center gap-3 rounded-2xl border bg-card p-4 shadow-sm">
                        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-orange-100">
                            <Flame size={20} className="text-orange-500" />
                        </div>
                        <div>
                            <p className="text-xs text-muted-foreground">Streak</p>
                            <p className="text-2xl font-bold">{stats.current_streak}<span className="text-sm font-normal text-muted-foreground"> days</span></p>
                        </div>
                    </div>
                    <div className="flex items-center gap-3 rounded-2xl border bg-card p-4 shadow-sm">
                        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100">
                            <Trophy size={20} className="text-purple-500" />
                        </div>
                        <div>
                            <p className="text-xs text-muted-foreground">Best Streak</p>
                            <p className="text-2xl font-bold">{stats.longest_streak}<span className="text-sm font-normal text-muted-foreground"> days</span></p>
                        </div>
                    </div>
                </div>

                {/* How to earn */}
                <div className="rounded-2xl border bg-emerald-50 p-4 dark:bg-emerald-950/20">
                    <p className="mb-2 text-sm font-semibold text-emerald-800 dark:text-emerald-300">How to earn points</p>
                    <ol className="space-y-1 text-xs text-emerald-700 dark:text-emerald-400">
                        <li>1. Tap <strong>My QR</strong> below to show your QR code</li>
                        <li>2. Hold it up to the Mobius bin camera</li>
                        <li>3. Deposit your cup/lid/straw</li>
                        <li>4. Points appear here automatically!</li>
                    </ol>
                </div>

                {/* Recent activity */}
                <div>
                    <p className="mb-3 text-sm font-semibold">Recent Activity</p>
                    {recentTransactions.length > 0 ? (
                        <div className="flex flex-col gap-2">
                            {recentTransactions.map((t, i) => (
                                <div key={i} className="flex items-center justify-between rounded-xl border bg-card px-4 py-3">
                                    <div>
                                        <p className="text-sm font-medium">{t.description ?? t.type}</p>
                                        <p className="text-xs text-muted-foreground">{t.date}</p>
                                    </div>
                                    <span className={`text-base font-bold ${typeColor[t.type] ?? 'text-foreground'}`}>
                                        {t.points > 0 ? '+' : ''}{t.points}
                                    </span>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="rounded-xl border bg-card px-4 py-8 text-center text-sm text-muted-foreground">
                            No activity yet. Start recycling to earn points!
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}

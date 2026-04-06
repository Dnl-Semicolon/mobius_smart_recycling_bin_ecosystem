import { Link, usePage } from '@inertiajs/react';
import { Home, QrCode, Ticket } from 'lucide-react';
import type { ReactNode } from 'react';

const tabs = [
    { href: '/public', icon: Home, label: 'Home' },
    { href: '/public/qr', icon: QrCode, label: 'My QR' },
    { href: '/public/vouchers', icon: Ticket, label: 'Vouchers' },
];

export default function MobileLayout({ children }: { children: ReactNode }) {
    const { url } = usePage();
    const { auth } = usePage<{ auth: { user: { name: string; points_balance: number } } }>().props;

    return (
        <div className="flex h-dvh flex-col bg-background">
            {/* Top bar */}
            <header className="flex items-center justify-between bg-emerald-600 px-4 py-3 text-white">
                <div>
                    <p className="text-xs font-medium opacity-75">Welcome back</p>
                    <p className="text-sm font-bold truncate max-w-[160px]">{auth?.user?.name}</p>
                </div>
                <div className="text-right">
                    <p className="text-xs font-medium opacity-75">Points Balance</p>
                    <p className="text-xl font-bold">{auth?.user?.points_balance ?? 0}</p>
                </div>
            </header>

            {/* Scrollable content */}
            <main className="flex-1 overflow-y-auto pb-2">
                {children}
            </main>

            {/* Bottom tab bar */}
            <nav className="border-t bg-background pb-safe">
                <div className="flex">
                    {tabs.map(({ href, icon: Icon, label }) => {
                        const isActive = url === href || (href !== '/public' && url.startsWith(href));
                        return (
                            <Link
                                key={href}
                                href={href}
                                className={`flex flex-1 flex-col items-center gap-1 py-2 text-xs font-medium transition-colors ${
                                    isActive
                                        ? 'text-emerald-600'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                <Icon size={22} strokeWidth={isActive ? 2.5 : 1.8} />
                                <span>{label}</span>
                            </Link>
                        );
                    })}
                </div>
            </nav>
        </div>
    );
}

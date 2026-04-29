import { Link, usePage } from '@inertiajs/react';
import { getStarted } from '@/routes';

type NavAnchor = {
    label: string;
    href: string;
};

const ANCHORS: NavAnchor[] = [
    { label: 'How it works', href: '#how-it-works' },
    { label: 'Technology', href: '#technology' },
    { label: 'Pricing', href: '#pricing' },
];

export function MarketingNav() {
    const { auth } = usePage().props as { auth: { user: { id: number } | null } };

    return (
        <header className="sticky top-0 z-50 border-b border-border bg-background/85 backdrop-blur">
            <div className="mx-auto flex max-w-[1280px] items-center justify-between px-8 py-5">
                <Link
                    href="/"
                    className="text-base font-semibold tracking-tight text-foreground"
                >
                    Mobius
                </Link>

                <nav aria-label="Primary" className="flex items-center gap-8">
                    <ul className="flex items-center gap-7 text-sm text-muted-foreground">
                        {ANCHORS.map((anchor) => (
                            <li key={anchor.href}>
                                <a
                                    href={anchor.href}
                                    className="transition-colors hover:text-foreground"
                                >
                                    {anchor.label}
                                </a>
                            </li>
                        ))}
                    </ul>

                    <div className="h-5 w-px bg-border" aria-hidden />

                    {auth.user ? (
                        <Link
                            href="/dashboard"
                            className="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                        >
                            Dashboard
                        </Link>
                    ) : (
                        <Link
                            href={getStarted().url}
                            className="rounded-md bg-foreground px-4 py-2 text-sm font-medium text-background transition-opacity hover:opacity-85"
                        >
                            Become a partner
                        </Link>
                    )}
                </nav>
            </div>
        </header>
    );
}

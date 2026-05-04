import { Link } from '@inertiajs/react';
import { login } from '@/routes';

export function MarketingFooter() {
    const year = new Date().getFullYear();

    return (
        <footer className="border-t border-border bg-background">
            <div className="mx-auto flex max-w-[1280px] flex-col gap-6 px-5 py-10 text-xs text-muted-foreground md:flex-row md:items-start md:justify-between md:gap-8 md:px-8 md:py-12">
                <div className="flex flex-col gap-1.5">
                    <span className="font-semibold text-foreground">Mobius Vision Enterprise</span>
                    <span className="tabular-nums">202503229755 (IP0612668-K)</span>
                    <span className="text-muted-foreground/80">© {year}. All rights reserved.</span>
                </div>
                <Link
                    href={login.url()}
                    className="self-start text-foreground/70 underline-offset-4 hover:text-foreground hover:underline md:self-auto"
                >
                    Sign in
                </Link>
            </div>
        </footer>
    );
}

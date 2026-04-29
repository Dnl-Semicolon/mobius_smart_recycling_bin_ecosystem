import { Link } from '@inertiajs/react';
import { login } from '@/routes';

export function MarketingFooter() {
    const year = new Date().getFullYear();

    return (
        <footer className="border-t border-border bg-background">
            <div className="mx-auto flex max-w-[1280px] flex-col gap-4 px-5 py-8 text-xs text-muted-foreground md:flex-row md:items-center md:justify-between md:px-8 md:py-10">
                <span>Mobius. Recycling infrastructure for beverage brands. © {year}.</span>
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

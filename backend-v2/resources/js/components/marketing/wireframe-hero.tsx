import { Link } from '@inertiajs/react';
import { getStarted } from '@/routes';

/**
 * Wireframe hero: asymmetric two-column (60/40 text-left, visual-right).
 * Per docs/shape/01-Hero.md section 5. Mid-fi: real CTAs, labelled placeholders
 * for headline/subhead/visual.
 */
export function WireframeHero() {
    return (
        <section className="relative border-b border-border" aria-labelledby="hero-heading">
            <div className="mx-auto grid max-w-[1280px] grid-cols-12 gap-8 px-8 pt-24 pb-32">
                <div className="col-span-7 flex flex-col justify-center">
                    <PlaceholderLabel>HEADLINE / 8–12 words / period-terminated</PlaceholderLabel>

                    <h1
                        id="hero-heading"
                        className="mt-4 text-[clamp(2.5rem,5vw,4.5rem)] font-semibold leading-[1.05] tracking-tight text-foreground"
                        style={{ textWrap: 'balance' }}
                    >
                        [Declarative claim about recycling-revenue infrastructure for beverage brands.]
                    </h1>

                    <PlaceholderLabel className="mt-10">SUBHEAD / 22–35 words / two sentences max</PlaceholderLabel>

                    <p className="mt-3 max-w-[58ch] text-lg leading-relaxed text-muted-foreground">
                        [Supporting statement explaining the end-to-end build, the partnership model, and the brand return. Mobius is the team beverage brands hire.]
                    </p>

                    <div className="mt-12 flex items-center gap-6">
                        <Link
                            href={getStarted().url}
                            className="rounded-md bg-foreground px-6 py-3 text-sm font-medium text-background transition-opacity hover:opacity-85"
                        >
                            Become a partner
                        </Link>
                        <a
                            href="#how-it-works"
                            className="text-sm font-medium text-foreground underline-offset-4 hover:underline"
                        >
                            See how it works
                            <span aria-hidden> →</span>
                        </a>
                    </div>
                </div>

                <div className="col-span-5 flex flex-col">
                    <PlaceholderLabel>HERO_VISUAL</PlaceholderLabel>
                    <div className="mt-3 flex flex-1 items-center justify-center rounded-lg border border-dashed border-border bg-muted">
                        <div className="px-8 py-16 text-center">
                            <p className="text-xs font-medium uppercase tracking-[0.2em] text-muted-foreground">
                                AI-gen abstract
                            </p>
                            <p className="mt-2 text-xs text-muted-foreground">
                                /  product render  /  dataviz
                            </p>
                            <p className="mt-8 text-[10px] uppercase tracking-[0.18em] text-muted-foreground/70">
                                per-spin replacement
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}

function PlaceholderLabel({
    children,
    className = '',
}: {
    children: React.ReactNode;
    className?: string;
}) {
    return (
        <span
            className={`inline-flex items-center self-start rounded-sm bg-muted px-2 py-1 text-[10px] font-medium uppercase tracking-[0.18em] text-muted-foreground ${className}`}
        >
            {children}
        </span>
    );
}

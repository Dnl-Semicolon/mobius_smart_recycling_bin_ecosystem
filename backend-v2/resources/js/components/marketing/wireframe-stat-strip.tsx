type Stat = {
    figure: string;
    label: string;
    note?: string;
};

const WIREFRAME_STATS: Stat[] = [
    {
        figure: '4 billion',
        label: 'disposable cups discarded yearly in Malaysia',
        note: 'sector estimate',
    },
    {
        figure: '12,000+',
        label: 'branded beverage outlets nationwide',
        note: 'sector estimate',
    },
    {
        figure: '1.5×',
        label: 'loyalty multiplier on brand-match cup recycling',
        note: 'Mobius rewards engine',
    },
    {
        figure: '0%',
        label: 'handled by today’s general bin networks',
        note: 'baseline',
    },
];

/**
 * Stats strip below the hero. Stripe-mirror placement: separator hairline above,
 * four cells in a horizontal row at desktop. Uses tabular numerals for alignment.
 */
export function WireframeStatStrip() {
    return (
        <section className="border-b border-border bg-background" aria-labelledby="stat-strip-heading">
            <h2 id="stat-strip-heading" className="sr-only">
                Sector context
            </h2>
            <div className="mx-auto max-w-[1280px] px-5 py-12 md:px-8 md:py-16">
                <p className="mb-8 text-[10px] font-medium uppercase tracking-[0.22em] text-muted-foreground md:mb-10">
                    Sector context · placeholder figures
                </p>
                <dl className="grid grid-cols-2 gap-x-8 gap-y-8 md:grid-cols-2 md:gap-x-12 md:gap-y-10 lg:grid-cols-4">
                    {WIREFRAME_STATS.map((stat) => (
                        <div key={stat.figure} className="flex flex-col gap-2 md:gap-3">
                            <dt
                                className="text-2xl font-semibold tracking-tight text-foreground tabular-nums md:text-3xl lg:text-4xl"
                                style={{ fontVariantNumeric: 'tabular-nums' }}
                            >
                                {stat.figure}
                            </dt>
                            <dd className="text-sm leading-snug text-muted-foreground">
                                {stat.label}
                            </dd>
                            {stat.note && (
                                <p className="text-[10px] uppercase tracking-[0.18em] text-muted-foreground/70">
                                    {stat.note}
                                </p>
                            )}
                        </div>
                    ))}
                </dl>
            </div>
        </section>
    );
}

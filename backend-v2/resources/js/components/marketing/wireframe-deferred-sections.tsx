type DeferredSection = {
    id: string;
    label: string;
    summary: string;
};

const DEFERRED: DeferredSection[] = [
    {
        id: 'how-it-works',
        label: '02 · How it works + dual-brand AI explainer',
        summary:
            'Three-step recycler flow paired with the cup × bin brand-match mechanic that drives the loyalty multiplier. Shape brief pending.',
    },
    {
        id: 'audience-proof',
        label: '03 · Three-audience proof + live impact counter',
        summary:
            'For brands / for recyclers / for councils. Each panel carries its own value prop, supporting evidence, and CTA. Shape brief pending.',
    },
    {
        id: 'technology',
        label: '04 · Route optimisation + IoT showcase',
        summary:
            'Map visual, OSRM and VROOM auto-dispatch story, smart-bin photography. Sells the end-to-end ecosystem claim. Shape brief pending.',
    },
    {
        id: 'pricing',
        label: '05 · Pricing and partnership tiers',
        summary:
            'Either an in-page section or a dedicated /pricing route depending on the spin. Carries Basic, Premium, and Custom tiers from the existing Plan model. Shape brief pending.',
    },
    {
        id: 'team',
        label: '06 · Team',
        summary:
            'Mobius framed as solution-makers, not a student project. Founders and operators view, no FYP origin. Shape brief pending.',
    },
];

/**
 * Wireframe deferred-sections placeholder. The Hero brief locks scope to the
 * hero only; this block communicates that the rest of the page is planned but
 * out of scope for this iteration. Each cell is a labelled grayscale block.
 */
export function WireframeDeferredSections() {
    return (
        <section
            className="border-b border-border bg-muted/40"
            aria-labelledby="deferred-heading"
        >
            <div className="mx-auto max-w-[1280px] px-5 py-14 md:px-8 md:py-20">
                <p
                    id="deferred-heading"
                    className="mb-8 text-[10px] font-medium uppercase tracking-[0.22em] text-muted-foreground md:mb-10"
                >
                    Future sections · shape briefs in flight
                </p>
                <ol className="flex flex-col gap-4 md:gap-6">
                    {DEFERRED.map((section) => (
                        <li
                            key={section.id}
                            id={section.id}
                            className="grid grid-cols-1 gap-4 border border-dashed border-border bg-background px-5 py-7 md:grid-cols-12 md:gap-8 md:px-6 md:py-10"
                        >
                            <div className="md:col-span-4">
                                <p className="text-[10px] font-medium uppercase tracking-[0.2em] text-muted-foreground/80">
                                    Section
                                </p>
                                <p className="mt-2 text-sm font-semibold tracking-tight text-foreground">
                                    {section.label}
                                </p>
                            </div>
                            <p className="text-sm leading-relaxed text-muted-foreground md:col-span-8">
                                {section.summary}
                            </p>
                        </li>
                    ))}
                </ol>
            </div>
        </section>
    );
}

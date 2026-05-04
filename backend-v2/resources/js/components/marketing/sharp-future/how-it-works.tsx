import { motion, useReducedMotion } from 'motion/react';

const STEPS = [
    {
        index: '01',
        title: 'Customer scans the bin',
        body: 'The QR on every Mobius bin opens the app already aware of the outlet, the host brand, and the disposal slot, so the customer never has to log in or pick a category before dropping a cup.',
    },
    {
        index: '02',
        title: 'AI sees what went in',
        body: 'Computer vision identifies the waste type and the cup brand at the moment of disposal, separating cup from lid from straw from liquid in the same frame while a parallel classifier tags the brand on the cup.',
    },
    {
        index: '03',
        title: 'Points settle to the brand',
        body: 'Matching cups earn the brand multiplier and write to your loyalty ledger, while competitor cups still get sorted but earn less. The next collection route updates in the background once the bin reports its new fill level.',
    },
];

const MATRIX = [
    {
        rowLabel: 'Brand match',
        outcome: '1.5×',
        outcomeKind: 'bonus' as const,
        body: 'When a customer drops your cup into your bin the full brand multiplier applies, and the loyalty card on their account increments against your ledger.',
    },
    {
        rowLabel: 'Unidentified',
        outcome: '1.0×',
        outcomeKind: 'neutral' as const,
        body: 'When the classifier cannot read the brand because the cup is smudged or unfamiliar, base points still apply and the cup is still sorted into the right waste stream.',
    },
    {
        rowLabel: 'Competitor cup',
        outcome: '0.3×',
        outcomeKind: 'deterrent' as const,
        body: 'When a competitor cup lands in your bin the multiplier drops well below base, which keeps your bin from quietly subsidising the rival brand whose cup the customer is throwing away.',
    },
];

export function HowItWorks() {
    const prefersReducedMotion = useReducedMotion();
    const fadeInitial = prefersReducedMotion ? false : { opacity: 0, y: 16 };

    return (
        <section
            id="how-it-works"
            aria-labelledby="how-it-works-heading"
            className="relative border-b border-border bg-card/30"
        >
            <div className="mx-auto max-w-[1280px] px-5 pt-24 pb-28 md:px-8 md:pt-28 md:pb-32 lg:pt-32 lg:pb-40">
                <motion.header
                    initial={fadeInitial}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true, margin: '-80px' }}
                    transition={{ duration: 0.6, ease: [0.22, 1, 0.36, 1] }}
                    className="max-w-[60ch]"
                >
                    <p className="text-xs font-medium uppercase tracking-[0.22em] text-primary">
                        How it works
                    </p>
                    <h2
                        id="how-it-works-heading"
                        className="mt-5 text-[clamp(2rem,4.5vw,3.75rem)] font-semibold leading-[1.1] tracking-[-0.02em] text-foreground"
                        style={{ textWrap: 'balance' }}
                    >
                        A customer drops a cup, and within seconds we know which cup it was, which brand made it, and which outlet sent it on its way.
                    </h2>
                </motion.header>

                <div className="mt-16 grid grid-cols-1 gap-x-8 gap-y-16 lg:mt-20 lg:grid-cols-12">
                    <div className="lg:col-span-7">
                        <p className="text-[10px] font-medium uppercase tracking-[0.2em] text-muted-foreground">
                            On the customer side
                        </p>
                        <ol className="mt-6 flex flex-col gap-10 md:gap-12">
                            {STEPS.map((step, i) => (
                                <motion.li
                                    key={step.index}
                                    initial={fadeInitial}
                                    whileInView={{ opacity: 1, y: 0 }}
                                    viewport={{ once: true, margin: '-60px' }}
                                    transition={{
                                        delay: i * 0.08,
                                        duration: 0.55,
                                        ease: [0.22, 1, 0.36, 1],
                                    }}
                                    className="relative grid grid-cols-[auto_1fr] gap-x-6 md:gap-x-8"
                                >
                                    <div className="flex flex-col items-center gap-3">
                                        <span className="font-mono text-sm font-medium tabular-nums text-primary">
                                            {step.index}
                                        </span>
                                        {i < STEPS.length - 1 && (
                                            <span className="block w-px flex-1 bg-border" aria-hidden />
                                        )}
                                    </div>
                                    <div className="pb-2">
                                        <h3 className="text-xl font-semibold tracking-tight text-foreground md:text-2xl">
                                            {step.title}
                                        </h3>
                                        <p className="mt-3 max-w-[55ch] text-sm leading-relaxed text-muted-foreground md:text-base">
                                            {step.body}
                                        </p>
                                    </div>
                                </motion.li>
                            ))}
                        </ol>
                    </div>

                    <div className="lg:col-span-5">
                        <p className="text-[10px] font-medium uppercase tracking-[0.2em] text-muted-foreground">
                            On the brand side
                        </p>
                        <h3 className="mt-6 text-xl font-semibold tracking-tight text-foreground md:text-2xl">
                            Cup brand × bin brand
                        </h3>
                        <p className="mt-3 max-w-[40ch] text-sm leading-relaxed text-muted-foreground md:text-base">
                            Every detection runs two classifiers. The point engine reads both axes and applies the rule that protects your brand from being a free dropoff for competitors.
                        </p>

                        <ul className="mt-8 flex flex-col gap-3">
                            {MATRIX.map((row, i) => (
                                <motion.li
                                    key={row.rowLabel}
                                    initial={fadeInitial}
                                    whileInView={{ opacity: 1, y: 0 }}
                                    viewport={{ once: true, margin: '-60px' }}
                                    transition={{
                                        delay: 0.2 + i * 0.08,
                                        duration: 0.55,
                                        ease: [0.22, 1, 0.36, 1],
                                    }}
                                    className={
                                        row.outcomeKind === 'bonus'
                                            ? 'border border-primary/40 bg-primary/8 backdrop-blur-sm'
                                            : row.outcomeKind === 'deterrent'
                                              ? 'border border-border/70 bg-muted/30'
                                              : 'border border-border bg-card/40'
                                    }
                                >
                                    <div className="flex items-baseline gap-4 px-5 py-4 md:px-6 md:py-5">
                                        <span
                                            className={
                                                row.outcomeKind === 'bonus'
                                                    ? 'font-mono text-2xl font-semibold tabular-nums text-primary md:text-3xl'
                                                    : row.outcomeKind === 'deterrent'
                                                      ? 'font-mono text-2xl font-semibold tabular-nums text-muted-foreground md:text-3xl'
                                                      : 'font-mono text-2xl font-semibold tabular-nums text-foreground md:text-3xl'
                                            }
                                            style={{ fontVariantNumeric: 'tabular-nums' }}
                                        >
                                            {row.outcome}
                                        </span>
                                        <span className="text-[10px] font-medium uppercase tracking-[0.18em] text-muted-foreground">
                                            {row.rowLabel}
                                        </span>
                                    </div>
                                    <p className="border-t border-border/60 px-5 py-4 text-xs leading-relaxed text-muted-foreground md:px-6 md:text-sm">
                                        {row.body}
                                    </p>
                                </motion.li>
                            ))}
                        </ul>

                        <p className="mt-6 text-xs leading-relaxed text-muted-foreground/80">
                            Multipliers are configurable per partner. Defaults shown.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    );
}

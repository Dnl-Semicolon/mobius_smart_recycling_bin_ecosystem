import { motion, useReducedMotion } from 'motion/react';

const IOT_SPECS = [
    { figure: '4G', label: 'cellular uplink', detail: 'with sim swap supported' },
    { figure: 'Cloud', label: 'classifier and ledger', detail: 'bin runs as the client' },
];

export function RouteAndIot() {
    const prefersReducedMotion = useReducedMotion();
    const fadeInitial = prefersReducedMotion ? false : { opacity: 0, y: 16 };

    return (
        <section
            id="technology"
            aria-labelledby="route-heading"
            className="relative border-b border-border bg-background"
        >
            <div className="mx-auto max-w-[1280px] px-5 pt-24 pb-12 md:px-8 md:pt-28 md:pb-16 lg:pt-32">
                <motion.header
                    initial={fadeInitial}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true, margin: '-80px' }}
                    transition={{ duration: 0.6, ease: [0.22, 1, 0.36, 1] }}
                    className="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-12"
                >
                    <div className="lg:col-span-5">
                        <p className="text-xs font-medium uppercase tracking-[0.22em] text-primary">
                            How the ecosystem actually runs
                        </p>
                        <h2
                            id="route-heading"
                            className="mt-5 text-[clamp(2rem,4.5vw,3.5rem)] font-semibold leading-[1.1] tracking-[-0.02em] text-foreground"
                            style={{ textWrap: 'balance' }}
                        >
                            Bins call for pickup as soon as they fill, and the route engine re-plans the next collection in the same minute.
                        </h2>
                    </div>
                    <p className="max-w-[55ch] text-base leading-relaxed text-muted-foreground md:text-lg lg:col-span-7">
                        Every Mobius bin reports fill state continuously. When a zone crosses threshold, the routing engine solves the vehicle problem with OSRM and VROOM, dispatches the next collection, and updates ETAs for every driver that shares the zone, with operators tracking the run on a dashboard while customers experience nothing different at the bin.
                    </p>
                </motion.header>
            </div>

            <div className="border-y border-border bg-card/30">
                <div className="mx-auto flex max-w-[1440px] flex-col items-stretch gap-y-6 px-5 py-14 md:flex-row md:gap-x-10 md:gap-y-0 md:px-8 md:py-16 lg:gap-x-16 lg:py-20">
                    <div className="md:w-1/3 lg:w-2/5">
                        <p className="text-[10px] font-medium uppercase tracking-[0.2em] text-muted-foreground">
                            Penang pilot
                        </p>
                        <h3 className="mt-3 text-lg font-semibold leading-snug text-foreground md:text-xl">
                            A live map of pilot bins across the island lands here.
                        </h3>
                    </div>
                    <div className="flex flex-1 items-center justify-center rounded-lg border border-dashed border-border bg-card/40 p-10">
                        <p className="font-mono text-[10px] uppercase tracking-[0.22em] text-muted-foreground/80">
                            Map preview pending
                        </p>
                    </div>
                </div>
            </div>

            <div className="mx-auto max-w-[1280px] px-5 py-14 md:px-8 md:py-16">
                <p className="text-[10px] font-medium uppercase tracking-[0.2em] text-muted-foreground">
                    Inside the bin
                </p>
                <dl className="mt-6 grid grid-cols-1 gap-x-12 gap-y-8 md:grid-cols-2">
                    {IOT_SPECS.map((spec, i) => (
                        <motion.div
                            key={spec.figure}
                            initial={fadeInitial}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true, margin: '-60px' }}
                            transition={{
                                delay: i * 0.08,
                                duration: 0.5,
                                ease: [0.22, 1, 0.36, 1],
                            }}
                            className="flex flex-col gap-2"
                        >
                            <dt
                                className="font-mono text-3xl font-semibold leading-none tracking-tight text-foreground tabular-nums md:text-4xl"
                                style={{ fontVariantNumeric: 'tabular-nums' }}
                            >
                                {spec.figure}
                            </dt>
                            <dd className="flex flex-col gap-1">
                                <span className="text-sm font-medium text-foreground">{spec.label}</span>
                                <span className="text-xs text-muted-foreground">{spec.detail}</span>
                            </dd>
                        </motion.div>
                    ))}
                </dl>
            </div>
        </section>
    );
}

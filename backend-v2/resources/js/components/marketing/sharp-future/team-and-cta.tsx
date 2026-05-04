import { Link } from '@inertiajs/react';
import { motion, useReducedMotion } from 'motion/react';
import { getStarted } from '@/routes';

const PRINCIPLES = [
    {
        figure: '01',
        title: 'The team running it built it.',
        body: 'Every Mobius bin in the field is monitored, serviced, and improved by the same group that designed it, so there is never a second-tier vendor sitting between you and the operation.',
    },
    {
        figure: '02',
        title: 'The bin, the AI, and the software ship together.',
        body: 'Hardware, the detector, the rewards layer, and the route engine are built and deployed as one product, which means there is no integration tax and no finger-pointing when something needs fixing in the field.',
    },
    {
        figure: '03',
        title: 'Built for Malaysian outlets first.',
        body: 'Mobius is registered in Malaysia and tooled in Malaysia for Malaysian beverage outlets and councils. Compliance, support, and replacement parts all live in the same time zone, and any expansion abroad has to clear that operating bar first.',
    },
];

export function TeamAndCta() {
    const prefersReducedMotion = useReducedMotion();
    const fadeInitial = prefersReducedMotion ? false : { opacity: 0, y: 16 };

    return (
        <section
            id="team"
            aria-labelledby="team-heading"
            className="relative border-b border-border bg-background"
        >
            <div className="mx-auto max-w-[1280px] px-5 pt-24 pb-24 md:px-8 md:pt-28 md:pb-28 lg:pt-32 lg:pb-32">
                <motion.header
                    initial={fadeInitial}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true, margin: '-80px' }}
                    transition={{ duration: 0.6, ease: [0.22, 1, 0.36, 1] }}
                    className="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-12"
                >
                    <div className="lg:col-span-7">
                        <p className="text-xs font-medium uppercase tracking-[0.22em] text-primary">
                            The team behind it
                        </p>
                        <h2
                            id="team-heading"
                            className="mt-5 text-[clamp(2rem,4.5vw,3.5rem)] font-semibold leading-[1.1] tracking-[-0.02em] text-foreground"
                            style={{ textWrap: 'balance' }}
                        >
                            Built and run by the same operating team that ships into your outlets.
                        </h2>
                    </div>
                    <p className="max-w-[55ch] text-base leading-relaxed text-muted-foreground md:text-lg lg:col-span-5">
                        Mobius is a Malaysian-registered operating company (Mobius Vision Enterprise, 202503229755). Hardware, AI models, mobile, dashboard, and route engine are designed and shipped in-house. We answer the support tickets ourselves.
                    </p>
                </motion.header>

                <div className="mt-16 grid grid-cols-1 gap-6 md:gap-8 lg:mt-20 lg:grid-cols-3">
                    {PRINCIPLES.map((p, i) => (
                        <motion.article
                            key={p.figure}
                            initial={fadeInitial}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true, margin: '-60px' }}
                            transition={{
                                delay: i * 0.08,
                                duration: 0.55,
                                ease: [0.22, 1, 0.36, 1],
                            }}
                            className="flex flex-col border-t border-border pt-8"
                        >
                            <span className="font-mono text-xs font-medium tabular-nums text-primary">
                                {p.figure}
                            </span>
                            <h3 className="mt-4 text-lg font-semibold leading-[1.25] tracking-tight text-foreground md:text-xl">
                                {p.title}
                            </h3>
                            <p className="mt-3 text-sm leading-relaxed text-muted-foreground md:text-base">
                                {p.body}
                            </p>
                        </motion.article>
                    ))}
                </div>
            </div>

            <div className="border-t border-border bg-card/40">
                <div className="mx-auto flex max-w-[1280px] flex-col items-start gap-8 px-5 py-16 md:px-8 md:py-20 lg:flex-row lg:items-center lg:justify-between lg:gap-12">
                    <div className="max-w-[55ch]">
                        <p className="text-xs font-medium uppercase tracking-[0.22em] text-primary">
                            Talk to us
                        </p>
                        <h2 className="mt-4 text-[clamp(1.75rem,3.5vw,2.75rem)] font-semibold leading-[1.1] tracking-[-0.02em] text-foreground">
                            Bring Mobius into your outlets.
                        </h2>
                        <p className="mt-4 text-sm leading-relaxed text-muted-foreground md:text-base">
                            We will sit down with your operations or sustainability lead and walk through what changes inside your stores once a Mobius bin is installed at the dispatch point and the detection feed starts writing to your loyalty data.
                        </p>
                    </div>
                    <div className="flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:gap-5">
                        <Link
                            href={getStarted().url}
                            className="rounded-full bg-primary px-7 py-3.5 text-sm font-semibold text-primary-foreground transition-transform hover:-translate-y-0.5"
                        >
                            Become a partner
                        </Link>
                        <a
                            href="#procurement"
                            className="text-sm font-semibold text-foreground underline-offset-4 hover:underline"
                        >
                            Request the procurement deck
                            <span aria-hidden className="ml-2 text-primary">›</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    );
}

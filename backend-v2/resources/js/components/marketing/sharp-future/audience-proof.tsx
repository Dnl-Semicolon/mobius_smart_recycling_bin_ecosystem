import { Link } from '@inertiajs/react';
import { motion, useReducedMotion } from 'motion/react';
import { getStarted } from '@/routes';

const AUDIENCES = [
    {
        eyebrow: 'For brands',
        title: 'Recover the value your cups carry out the door.',
        body: 'Mobius runs the bins inside your outlets, ties every detection back to your loyalty ledger, and exports brand-attributable recycling reports your sustainability team can sign off on.',
        bullets: [
            { figure: '01', label: 'Per-outlet capture, audit-ready.' },
            { figure: '02', label: 'Brand multiplier on cup match.' },
            { figure: '03', label: 'Competitor deterrent built in.' },
        ],
        cta: { label: 'Become a partner', href: getStarted().url, primary: true },
    },
    {
        eyebrow: 'For recyclers',
        title: 'A simpler way to see the cup you just dropped become a reward.',
        body: 'Scanning the QR on any Mobius bin opens the app already aware of which outlet you are at and which brand you are dropping into, so points settle within seconds and the next nearby bin is one tap away.',
        bullets: [
            { figure: '01', label: 'No login pressure at the bin.' },
            { figure: '02', label: 'Streaks, vouchers, and brand bonuses.' },
            { figure: '03', label: 'Map of bins across your city.' },
        ],
        cta: { label: 'Get the app', href: '#app-store', primary: false },
    },
    {
        eyebrow: 'For councils and agencies',
        title: 'A recycling stream the city can audit directly.',
        body: 'Every cup is timestamped, geo-located, and tagged to the brand that issued it, then written to an audit ledger your office can query without waiting on vendor screenshots or annualised estimates.',
        bullets: [
            { figure: '01', label: 'Per-zone, per-brand, per-day reports.' },
            { figure: '02', label: 'Deployable with existing collection contracts.' },
            { figure: '03', label: 'Compliance-ready data export.' },
        ],
        cta: { label: 'Request the procurement deck', href: '#procurement', primary: false },
    },
];

export function AudienceProof() {
    const prefersReducedMotion = useReducedMotion();
    const fadeInitial = prefersReducedMotion ? false : { opacity: 0, y: 16 };

    return (
        <section
            id="audience-proof"
            aria-labelledby="audience-heading"
            className="relative border-b border-border bg-background"
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
                        Three sides of the same operation
                    </p>
                    <h2
                        id="audience-heading"
                        className="mt-5 text-[clamp(2rem,4.5vw,3.5rem)] font-semibold leading-[1.1] tracking-[-0.02em] text-foreground"
                        style={{ textWrap: 'balance' }}
                    >
                        One ecosystem serving the brands that fund it, the customers that use it, and the cities that audit it.
                    </h2>
                </motion.header>

                <div className="mt-16 grid grid-cols-1 gap-6 md:gap-8 lg:mt-20 lg:grid-cols-3">
                    {AUDIENCES.map((aud, i) => (
                        <motion.article
                            key={aud.eyebrow}
                            initial={fadeInitial}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true, margin: '-80px' }}
                            transition={{
                                delay: i * 0.1,
                                duration: 0.6,
                                ease: [0.22, 1, 0.36, 1],
                            }}
                            className={
                                aud.cta.primary
                                    ? 'group relative flex flex-col border border-primary/30 bg-primary/5 p-7 backdrop-blur-sm md:p-8'
                                    : 'group relative flex flex-col border border-border bg-card/40 p-7 backdrop-blur-sm md:p-8'
                            }
                        >
                            <p
                                className={
                                    aud.cta.primary
                                        ? 'text-[10px] font-medium uppercase tracking-[0.22em] text-primary'
                                        : 'text-[10px] font-medium uppercase tracking-[0.22em] text-muted-foreground'
                                }
                            >
                                {aud.eyebrow}
                            </p>
                            <h3 className="mt-4 text-xl font-semibold leading-[1.2] tracking-tight text-foreground md:text-2xl">
                                {aud.title}
                            </h3>
                            <p className="mt-4 text-sm leading-relaxed text-muted-foreground md:text-base">
                                {aud.body}
                            </p>

                            <ul className="mt-6 flex flex-col gap-3 border-t border-border/60 pt-6">
                                {aud.bullets.map((bullet) => (
                                    <li key={bullet.figure} className="grid grid-cols-[auto_1fr] gap-x-4 text-sm">
                                        <span className="font-mono text-xs font-medium tabular-nums text-muted-foreground/70">
                                            {bullet.figure}
                                        </span>
                                        <span className="text-foreground">{bullet.label}</span>
                                    </li>
                                ))}
                            </ul>

                            <div className="mt-8 pt-2">
                                <Link
                                    href={aud.cta.href}
                                    className={
                                        aud.cta.primary
                                            ? 'inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition-transform hover:-translate-y-0.5'
                                            : 'inline-flex items-center gap-2 text-sm font-semibold text-foreground underline-offset-4 transition-colors hover:text-primary hover:underline'
                                    }
                                >
                                    {aud.cta.label}
                                    <span aria-hidden>›</span>
                                </Link>
                            </div>
                        </motion.article>
                    ))}
                </div>
            </div>
        </section>
    );
}

import { Link } from '@inertiajs/react';
import { motion, useReducedMotion } from 'motion/react';
import { getStarted } from '@/routes';

import { BinSchematic } from './bin-schematic';

const STATS: Array<{ caption: string; figure: string; suffix?: string }> = [
    { caption: 'Outlets we could cover', figure: '12,000', suffix: '+' },
    { caption: 'Annual cup volume', figure: '4', suffix: ' billion' },
    { caption: 'Loyalty uplift on cup match', figure: '1.5', suffix: '×' },
];

export function PrecisePremiumHero() {
    const prefersReducedMotion = useReducedMotion();
    const fadeIn = prefersReducedMotion ? false : { opacity: 0, y: 8 };

    return (
        <section className="border-b border-border" aria-labelledby="hero-heading">
            <div className="mx-auto grid max-w-[1280px] grid-cols-1 gap-x-8 gap-y-12 px-5 pt-16 pb-20 md:px-8 md:pt-20 md:pb-24 lg:grid-cols-12 lg:pt-24 lg:pb-28">
                <div className="flex flex-col justify-center lg:col-span-7">
                    <motion.p
                        initial={fadeIn}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.6, ease: [0.22, 1, 0.36, 1] }}
                        className="font-medium uppercase tracking-[0.18em] text-muted-foreground"
                        style={{ fontFamily: 'var(--font-mono)', fontSize: '11px' }}
                    >
                        Mobius / Hardware, AI, software
                    </motion.p>

                    <motion.h1
                        id="hero-heading"
                        initial={fadeIn}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.7, duration: 0.7, ease: [0.22, 1, 0.36, 1] }}
                        className="mt-5 text-[clamp(2rem,5vw,4rem)] font-medium leading-[1.04] tracking-tight text-foreground md:mt-6"
                        style={{ textWrap: 'balance' }}
                    >
                        We build your brand’s recycling system.
                    </motion.h1>

                    <motion.p
                        initial={fadeIn}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.85, duration: 0.7, ease: [0.22, 1, 0.36, 1] }}
                        className="mt-7 max-w-[58ch] text-base leading-relaxed text-muted-foreground md:text-lg"
                    >
                        Mobius is the team beverage brands hire to design, install, and operate their disposable-cup recycling layer. Hardware, AI, and software. All tailored to your outlets.
                    </motion.p>

                    <motion.div
                        initial={fadeIn}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 1.0, duration: 0.7, ease: [0.22, 1, 0.36, 1] }}
                        className="mt-10 flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:gap-6 md:mt-12"
                    >
                        <Link
                            href={getStarted().url}
                            className="rounded-md bg-primary px-6 py-3 text-sm font-medium text-primary-foreground transition-opacity hover:opacity-90"
                        >
                            Become a partner
                        </Link>
                        <a
                            href="#technology"
                            className="text-sm font-medium text-foreground underline-offset-4 hover:underline"
                        >
                            View the system
                            <span aria-hidden> →</span>
                        </a>
                    </motion.div>
                </div>

                <div className="lg:col-span-5">
                    <div className="aspect-[4/3.6] w-full border border-border bg-card lg:aspect-auto lg:h-full">
                        <BinSchematic />
                    </div>
                </div>
            </div>

            <div className="border-t border-border bg-card">
                <div className="mx-auto max-w-[1280px] px-5 py-12 md:px-8 md:py-14">
                    <dl className="grid grid-cols-1 gap-x-12 gap-y-10 md:grid-cols-3">
                        {STATS.map((stat, i) => (
                            <div key={stat.caption} className="flex flex-col gap-2">
                                <dt
                                    className="text-[10px] font-medium uppercase tracking-[0.2em] text-muted-foreground"
                                    style={{ fontFamily: 'var(--font-mono)' }}
                                >
                                    0{i + 1} · {stat.caption}
                                </dt>
                                <dd
                                    className="text-[clamp(1.75rem,3vw,2.5rem)] font-medium leading-none tracking-tight text-foreground"
                                    style={{
                                        fontFamily: 'var(--font-mono)',
                                        fontVariantNumeric: 'tabular-nums',
                                    }}
                                >
                                    {stat.figure}
                                    {stat.suffix && (
                                        <span className="ml-0.5 text-muted-foreground">
                                            {stat.suffix}
                                        </span>
                                    )}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </div>
            </div>
        </section>
    );
}

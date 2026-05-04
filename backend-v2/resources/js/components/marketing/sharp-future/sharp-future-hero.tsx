import { Link } from '@inertiajs/react';
import { motion, useReducedMotion } from 'motion/react';
import { getStarted } from '@/routes';

import { HeroAurora } from './hero-aurora';

const HEADLINE_WORDS = [
    'Recycling',
    'ecosystem',
    'for',
    'beverage',
    'brands.',
];

const STATS = [
    { figure: '4B', label: 'disposable cups discarded yearly in Malaysia' },
    { figure: '12,000+', label: 'branded beverage outlets nationwide' },
    { figure: '0%', label: 'recovered by today’s bin networks' },
];

export function SharpFutureHero() {
    const prefersReducedMotion = useReducedMotion();
    const wordInitial = prefersReducedMotion ? false : { opacity: 0, y: '0.4em' };
    const fadeInitial = prefersReducedMotion ? false : { opacity: 0, y: 12 };

    return (
        <section className="relative overflow-hidden border-b border-border" aria-labelledby="hero-heading">
            <HeroAurora />

            <div className="relative mx-auto grid min-h-[calc(100vh-220px)] max-w-[1280px] grid-cols-1 content-center gap-y-10 px-5 py-16 md:px-8 md:py-20 lg:grid-cols-12 lg:py-12">
                <div className="lg:col-span-9">
                    <h1
                        id="hero-heading"
                        className="text-[clamp(2.75rem,6.5vw,5.5rem)] font-semibold leading-[1.05] tracking-[-0.02em] text-foreground"
                        style={{ textWrap: 'balance' }}
                    >
                        {HEADLINE_WORDS.map((word, i) => (
                            <span
                                key={`${word}-${i}`}
                                className="inline-block align-baseline"
                            >
                                <motion.span
                                    initial={wordInitial}
                                    animate={{ opacity: 1, y: 0 }}
                                    transition={{
                                        delay: 0.2 + i * 0.08,
                                        duration: 0.6,
                                        ease: [0.22, 1, 0.36, 1],
                                    }}
                                    className="inline-block"
                                >
                                    {word}
                                    {i < HEADLINE_WORDS.length - 1 && ' '}
                                </motion.span>
                            </span>
                        ))}
                    </h1>

                    <motion.p
                        initial={fadeInitial}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.85, duration: 0.7, ease: [0.22, 1, 0.36, 1] }}
                        className="mt-8 max-w-[58ch] text-base leading-relaxed text-muted-foreground md:text-lg"
                    >
                        Every disposable cup leaving your outlets is brand value walking out the door, and Mobius is the recycling ecosystem that brings it back, ties each cup to your loyalty data, and runs collection logistics inside the network of stores you already operate.
                    </motion.p>

                    <motion.div
                        initial={fadeInitial}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 1.0, duration: 0.7, ease: [0.22, 1, 0.36, 1] }}
                        className="mt-10 flex flex-col items-start gap-5 sm:flex-row sm:items-center sm:gap-7 md:mt-14"
                    >
                        <Link
                            href={getStarted().url}
                            className="rounded-full bg-primary px-7 py-3.5 text-sm font-semibold text-primary-foreground transition-transform hover:-translate-y-0.5"
                        >
                            Become a partner
                        </Link>
                        <a
                            href="#docs"
                            className="text-sm font-semibold text-foreground underline-offset-4 hover:underline"
                        >
                            Read the docs
                            <span aria-hidden className="ml-2 text-primary">›</span>
                        </a>
                    </motion.div>
                </div>
            </div>

            <div className="relative border-t border-border bg-card/40 backdrop-blur-sm">
                <div className="mx-auto max-w-[1280px] px-5 py-8 md:px-8 md:py-10">
                    <ol className="flex flex-col gap-6 text-sm md:flex-row md:flex-wrap md:items-baseline md:gap-x-3 md:gap-y-4">
                        {STATS.map((stat, i) => (
                            <motion.li
                                key={stat.figure}
                                initial={fadeInitial}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{
                                    delay: 1.2 + i * 0.08,
                                    duration: 0.5,
                                    ease: [0.22, 1, 0.36, 1],
                                }}
                                className="md:after:mx-3 md:after:text-muted-foreground/60 md:after:content-['·'] md:last:after:content-none"
                            >
                                <span
                                    className="font-semibold text-primary tabular-nums"
                                    style={{ fontVariantNumeric: 'tabular-nums' }}
                                >
                                    {stat.figure}
                                </span>{' '}
                                <span className="text-muted-foreground">{stat.label}</span>
                            </motion.li>
                        ))}
                    </ol>
                </div>
            </div>
        </section>
    );
}

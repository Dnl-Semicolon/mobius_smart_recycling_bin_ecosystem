import { Link } from '@inertiajs/react';
import { motion, useReducedMotion } from 'motion/react';
import { getStarted } from '@/routes';

type Plan = {
    id: number;
    name: string;
    description: string;
    price_monthly: string;
    price_yearly: string;
    bin_limit: number | null;
    outlet_limit: number | null;
    staff_limit: number | null;
    api_access: boolean;
    features: Record<string, unknown>;
    is_active: boolean;
};

type Props = {
    plans?: Plan[];
};

const FALLBACK_PLANS = [
    {
        name: 'Basic',
        priceLabel: 'RM49',
        priceSuffix: '/ month',
        description: 'Single-outlet brands testing the recycling layer.',
        features: ['Up to 3 bins', 'AI waste + brand detection', 'Basic dashboard'],
        ctaLabel: 'Get started',
        primary: false,
    },
    {
        name: 'Premium',
        priceLabel: 'RM149',
        priceSuffix: '/ month',
        description: 'Multi-outlet brands scaling across cities.',
        features: ['Up to 15 bins', 'Route optimisation', 'Vouchers + advanced analytics', 'Priority support'],
        ctaLabel: 'Become a partner',
        primary: true,
    },
    {
        name: 'Custom',
        priceLabel: 'Let’s talk',
        priceSuffix: '',
        description: 'Government, multi-brand, or sector-wide deployments.',
        features: ['Unlimited bins and outlets', 'API + ERP integration', 'Dedicated success team'],
        ctaLabel: 'Contact sales',
        primary: false,
    },
];

export function PricingTeaser({ plans }: Props) {
    const prefersReducedMotion = useReducedMotion();
    const fadeInitial = prefersReducedMotion ? false : { opacity: 0, y: 16 };

    const cards = (plans?.length ? plans : FALLBACK_PLANS).map((plan, i) => {
        if ('priceLabel' in plan) {
            return plan;
        }
        const monthly = parseFloat(plan.price_monthly);
        const isCustom = monthly === 0;
        const features: string[] = [];
        const limit = plan.bin_limit;
        if (limit === null) features.push('Unlimited bins and outlets');
        else features.push(`Up to ${limit} bins`);
        if (plan.outlet_limit !== null && plan.bin_limit !== null) features.push(`Up to ${plan.outlet_limit} outlets`);
        features.push('AI waste and brand detection');
        if (plan.api_access) features.push('API + ERP integration');
        else features.push('Route optimisation');
        return {
            name: plan.name,
            priceLabel: isCustom ? 'Let’s talk' : `RM${monthly}`,
            priceSuffix: isCustom ? '' : '/ month',
            description: plan.description,
            features,
            ctaLabel: isCustom ? 'Contact sales' : i === 1 ? 'Become a partner' : 'Get started',
            primary: i === 1,
        };
    });

    return (
        <section
            id="pricing"
            aria-labelledby="pricing-heading"
            className="relative border-b border-border bg-background"
        >
            <div className="mx-auto max-w-[1280px] px-5 pt-24 pb-28 md:px-8 md:pt-28 md:pb-32 lg:pt-32 lg:pb-40">
                <motion.header
                    initial={fadeInitial}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true, margin: '-80px' }}
                    transition={{ duration: 0.6, ease: [0.22, 1, 0.36, 1] }}
                    className="grid grid-cols-1 gap-x-8 gap-y-6 lg:grid-cols-12"
                >
                    <div className="lg:col-span-7">
                        <p className="text-xs font-medium uppercase tracking-[0.22em] text-primary">
                            Pricing and partnership
                        </p>
                        <h2
                            id="pricing-heading"
                            className="mt-5 text-[clamp(2rem,4.5vw,3.5rem)] font-semibold leading-[1.1] tracking-[-0.02em] text-foreground"
                            style={{ textWrap: 'balance' }}
                        >
                            From a single outlet on the Basic tier to a city-wide deployment on Custom, the same operating team supports the entire spread.
                        </h2>
                    </div>
                    <p className="max-w-[55ch] text-base leading-relaxed text-muted-foreground md:text-lg lg:col-span-5">
                        Three tiers cover most beverage-brand scenarios, and larger deployments with custom hardware, dedicated zones, or government partnerships move into Custom where the conversation usually starts with a scoping call rather than a checkout.
                    </p>
                </motion.header>

                <div className="mt-16 grid grid-cols-1 gap-6 md:gap-8 lg:mt-20 lg:grid-cols-3">
                    {cards.map((card, i) => (
                        <motion.article
                            key={card.name}
                            initial={fadeInitial}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true, margin: '-80px' }}
                            transition={{
                                delay: i * 0.1,
                                duration: 0.6,
                                ease: [0.22, 1, 0.36, 1],
                            }}
                            className={
                                card.primary
                                    ? 'group relative flex flex-col border border-primary/40 bg-primary/5 p-7 backdrop-blur-sm md:p-8'
                                    : 'group relative flex flex-col border border-border bg-card/40 p-7 backdrop-blur-sm md:p-8'
                            }
                        >
                            {card.primary && (
                                <span className="absolute -top-3 left-7 rounded-full bg-primary px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-primary-foreground">
                                    Most partners
                                </span>
                            )}
                            <p
                                className={
                                    card.primary
                                        ? 'text-[10px] font-medium uppercase tracking-[0.22em] text-primary'
                                        : 'text-[10px] font-medium uppercase tracking-[0.22em] text-muted-foreground'
                                }
                            >
                                {card.name}
                            </p>
                            <div className="mt-4 flex items-baseline gap-2">
                                <span
                                    className="font-mono text-4xl font-semibold leading-none tracking-tight text-foreground tabular-nums md:text-5xl"
                                    style={{ fontVariantNumeric: 'tabular-nums' }}
                                >
                                    {card.priceLabel}
                                </span>
                                {card.priceSuffix && (
                                    <span className="text-sm text-muted-foreground">
                                        {card.priceSuffix}
                                    </span>
                                )}
                            </div>
                            <p className="mt-4 text-sm leading-relaxed text-muted-foreground md:text-base">
                                {card.description}
                            </p>

                            <ul className="mt-6 flex flex-1 flex-col gap-2.5 border-t border-border/60 pt-6 text-sm">
                                {card.features.map((feature) => (
                                    <li key={feature} className="grid grid-cols-[auto_1fr] gap-x-3">
                                        <span className="font-mono text-xs text-primary/80">+</span>
                                        <span className="text-foreground">{feature}</span>
                                    </li>
                                ))}
                            </ul>

                            <div className="mt-8">
                                <Link
                                    href={getStarted().url}
                                    className={
                                        card.primary
                                            ? 'inline-flex w-full items-center justify-center gap-2 rounded-full bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground transition-transform hover:-translate-y-0.5'
                                            : 'inline-flex w-full items-center justify-center gap-2 rounded-full border border-border px-5 py-3 text-sm font-semibold text-foreground transition-colors hover:border-primary/60 hover:text-primary'
                                    }
                                >
                                    {card.ctaLabel}
                                    <span aria-hidden>›</span>
                                </Link>
                            </div>
                        </motion.article>
                    ))}
                </div>

                <p className="mt-10 text-center text-xs text-muted-foreground">
                    Detailed plan comparison and yearly billing are available on <a href="#pricing-full" className="text-foreground underline-offset-4 hover:underline">the pricing page</a>.
                </p>
            </div>
        </section>
    );
}

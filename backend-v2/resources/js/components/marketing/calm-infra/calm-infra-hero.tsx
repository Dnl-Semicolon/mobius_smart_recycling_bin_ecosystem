import { Link } from '@inertiajs/react';
import { getStarted } from '@/routes';

import { BinNetworkMap } from './bin-network-map';

export function CalmInfraHero() {
    return (
        <section className="border-b border-border" aria-labelledby="hero-heading">
            <div className="mx-auto grid max-w-[1280px] grid-cols-1 gap-y-10 px-5 pt-16 pb-14 md:px-8 md:pt-20 md:pb-16 lg:grid-cols-12 lg:gap-x-8 lg:pt-24 lg:pb-20">
                <header className="lg:col-span-9">
                    <p className="text-[10px] font-medium uppercase tracking-[0.2em] text-muted-foreground">
                        Mobius · operating data, audited recycling
                    </p>
                    <h1
                        id="hero-heading"
                        className="mt-4 text-[clamp(2rem,5vw,4rem)] font-medium leading-[1.05] tracking-tight text-foreground md:mt-6"
                        style={{
                            fontFamily: 'var(--font-serif)',
                            textWrap: 'balance',
                        }}
                    >
                        Recycling infrastructure, deployed by brand, audited by city.
                    </h1>
                    <p className="mt-6 max-w-[58ch] text-base leading-relaxed text-muted-foreground md:text-lg">
                        Mobius operates branded smart-bin networks across beverage outlets, generating verifiable recycling streams that brands report and councils audit.
                    </p>
                </header>

                <aside
                    className="flex flex-col items-start gap-3 self-end text-sm text-muted-foreground lg:col-span-3 lg:items-stretch lg:border-l lg:border-border lg:pl-8"
                    aria-label="Network status"
                >
                    <div className="flex items-center gap-2">
                        <span className="relative flex h-2 w-2">
                            <span className="absolute inset-0 rounded-full bg-primary opacity-60 motion-safe:animate-ping" />
                            <span className="relative inline-block h-2 w-2 rounded-full bg-primary" />
                        </span>
                        <span className="text-xs font-medium uppercase tracking-[0.16em] text-foreground">
                            Live network
                        </span>
                    </div>
                    <p className="text-xs leading-relaxed">
                        Pilot deployments in Penang and Kuala Lumpur. Operating data is shared with partner brands and audited monthly by the relevant local authority.
                    </p>
                </aside>
            </div>

            <div className="mx-auto max-w-[1440px] border-t border-border bg-card">
                <BinNetworkMap />
            </div>

            <div className="mx-auto grid max-w-[1280px] grid-cols-1 gap-x-8 gap-y-10 px-5 py-12 md:px-8 md:py-16 lg:grid-cols-12">
                <div className="lg:col-span-8">
                    <p className="text-base leading-[1.7] text-foreground md:text-lg">
                        Across an estimated{' '}
                        <strong className="font-semibold text-foreground tabular-nums">12,000</strong>
                        {' '}branded beverage outlets in Malaysia, roughly{' '}
                        <strong className="font-semibold text-foreground tabular-nums">4 billion</strong>
                        {' '}disposable cups enter the waste stream every year. Mobius captures, identifies, and ties every cup back to the brand that issued it, applying a{' '}
                        <strong className="font-semibold text-foreground tabular-nums">1.5×</strong>
                        {' '}loyalty multiplier when a cup matches the host brand and writing the result to an audit ledger that councils can query.
                    </p>
                </div>

                <div className="flex flex-col items-start gap-4 lg:col-span-4 lg:items-stretch lg:gap-3">
                    <Link
                        href={getStarted().url}
                        className="inline-flex items-center justify-center rounded-md bg-primary px-6 py-3 text-sm font-medium text-primary-foreground transition-opacity hover:opacity-90"
                    >
                        Speak to our team
                        <span aria-hidden className="ml-2">→</span>
                    </Link>
                    <Link
                        href="/get-started"
                        className="inline-flex items-center text-sm font-medium text-foreground underline-offset-4 hover:underline"
                    >
                        Operating data and reports
                        <span aria-hidden className="ml-2 text-muted-foreground">↗</span>
                    </Link>
                </div>
            </div>
        </section>
    );
}

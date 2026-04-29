import { Head } from '@inertiajs/react';

import { MarketingFooter } from '@/components/marketing/marketing-footer';
import { MarketingNav } from '@/components/marketing/marketing-nav';
import { PrecisePremiumHero } from '@/components/marketing/precise-premium/precise-premium-hero';
import { WireframeDeferredSections } from '@/components/marketing/wireframe-deferred-sections';

export default function Welcome() {
    return (
        <>
            <Head title="Recycling infrastructure for beverage brands">
                <link rel="preconnect" href="https://api.fontshare.com" />
                <link rel="preconnect" href="https://cdn.fontshare.com" crossOrigin="anonymous" />
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://api.fontshare.com/v2/css?f=general-sans@400,500,600&display=swap"
                    rel="stylesheet"
                />
                <link
                    href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500&display=swap"
                    rel="stylesheet"
                />
            </Head>

            <div data-theme="precise-premium" className="min-h-screen bg-background font-sans text-foreground antialiased">
                <MarketingNav />
                <main>
                    <PrecisePremiumHero />
                    <WireframeDeferredSections />
                </main>
                <MarketingFooter />
            </div>
        </>
    );
}

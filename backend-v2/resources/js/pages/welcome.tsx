import { Head } from '@inertiajs/react';

import { AudienceProof } from '@/components/marketing/sharp-future/audience-proof';
import { HowItWorks } from '@/components/marketing/sharp-future/how-it-works';
import { MarketingFooter } from '@/components/marketing/marketing-footer';
import { MarketingNav } from '@/components/marketing/marketing-nav';
import { SharpFutureHero } from '@/components/marketing/sharp-future/sharp-future-hero';
import { WireframeDeferredSections } from '@/components/marketing/wireframe-deferred-sections';

export default function Welcome() {
    return (
        <>
            <Head title="Recycling infrastructure for beverage brands">
                <link rel="preconnect" href="https://api.fontshare.com" />
                <link rel="preconnect" href="https://cdn.fontshare.com" crossOrigin="anonymous" />
                <link
                    href="https://api.fontshare.com/v2/css?f=switzer@400,500,600,700&display=swap"
                    rel="stylesheet"
                />
            </Head>

            <div data-theme="sharp-future" className="min-h-screen bg-background font-sans text-foreground antialiased">
                <MarketingNav />
                <main>
                    <SharpFutureHero />
                    <HowItWorks />
                    <AudienceProof />
                    <WireframeDeferredSections />
                </main>
                <MarketingFooter />
            </div>
        </>
    );
}

import { Head } from '@inertiajs/react';

import { AudienceProof } from '@/components/marketing/sharp-future/audience-proof';
import { HowItWorks } from '@/components/marketing/sharp-future/how-it-works';
import { MarketingFooter } from '@/components/marketing/marketing-footer';
import { MarketingNav } from '@/components/marketing/marketing-nav';
import { PricingTeaser } from '@/components/marketing/sharp-future/pricing-teaser';
import { RouteAndIot } from '@/components/marketing/sharp-future/route-and-iot';
import { SharpFutureHero } from '@/components/marketing/sharp-future/sharp-future-hero';
import { WireframeDeferredSections } from '@/components/marketing/wireframe-deferred-sections';

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

export default function Welcome({ plans = [] }: { plans?: Plan[] }) {
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
                    <RouteAndIot />
                    <PricingTeaser plans={plans} />
                    <WireframeDeferredSections />
                </main>
                <MarketingFooter />
            </div>
        </>
    );
}

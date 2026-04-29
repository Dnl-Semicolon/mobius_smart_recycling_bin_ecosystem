import { Head } from '@inertiajs/react';

import { CalmInfraHero } from '@/components/marketing/calm-infra/calm-infra-hero';
import { MarketingFooter } from '@/components/marketing/marketing-footer';
import { MarketingNav } from '@/components/marketing/marketing-nav';
import { WireframeDeferredSections } from '@/components/marketing/wireframe-deferred-sections';

export default function Welcome() {
    return (
        <>
            <Head title="Recycling infrastructure for beverage brands">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=public-sans:400,500,600&display=swap"
                    rel="stylesheet"
                />
                <link
                    href="https://fonts.bunny.net/css?family=source-serif-4:400,500,600&display=swap"
                    rel="stylesheet"
                />
            </Head>

            <div data-theme="calm-infra" className="min-h-screen bg-background font-sans text-foreground antialiased">
                <MarketingNav />
                <main>
                    <CalmInfraHero />
                    <WireframeDeferredSections />
                </main>
                <MarketingFooter />
            </div>
        </>
    );
}

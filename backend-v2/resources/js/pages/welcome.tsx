import { Head } from '@inertiajs/react';

import { MarketingFooter } from '@/components/marketing/marketing-footer';
import { MarketingNav } from '@/components/marketing/marketing-nav';
import { WireframeDeferredSections } from '@/components/marketing/wireframe-deferred-sections';
import { WireframeHero } from '@/components/marketing/wireframe-hero';
import { WireframeStatStrip } from '@/components/marketing/wireframe-stat-strip';

export default function Welcome() {
    return (
        <>
            <Head title="Recycling infrastructure for beverage brands" />

            <div data-theme="wireframe" className="min-h-screen bg-background font-sans text-foreground antialiased">
                <MarketingNav />
                <main>
                    <WireframeHero />
                    <WireframeStatStrip />
                    <WireframeDeferredSections />
                </main>
                <MarketingFooter />
            </div>
        </>
    );
}

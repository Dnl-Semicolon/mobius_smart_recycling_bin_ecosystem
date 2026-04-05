import { Head, Link, usePage } from '@inertiajs/react';
import { login, register } from '@/routes';
// import { Check } from 'lucide-react'; // re-enable when features are designed

type Plan = {
    id: number;
    name: string;
    description: string;
    price_monthly: string;
    price_yearly: string;
    features: Record<string, unknown>;
    is_active: boolean;
};

export default function Welcome({
    canRegister = true,
    plans = [],
}: {
    canRegister?: boolean;
    plans?: Plan[];
}) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Mobius - Smart Recycling Ecosystem">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
            </Head>

            <div className="min-h-screen bg-white text-gray-900 dark:bg-gray-950 dark:text-gray-100">
                {/* Nav */}
                <header className="sticky top-0 z-50 border-b border-gray-200 bg-white/80 backdrop-blur dark:border-gray-800 dark:bg-gray-950/80">
                    <div className="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                        <div className="text-xl font-bold tracking-tight">
                            Mobius
                        </div>
                        <nav className="flex items-center gap-6 text-sm">
                            <a href="#pricing" className="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                Pricing
                            </a>
                            <a href="#how-it-works" className="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                How It Works
                            </a>
                            {auth.user && (
                                <Link
                                    href="/dashboard"
                                    className="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                                >
                                    Dashboard
                                </Link>
                            )}
                        </nav>
                    </div>
                </header>

                {/* Hero */}
                <section className="mx-auto max-w-6xl px-6 py-24 text-center lg:py-32">
                    <h1 className="text-4xl font-bold tracking-tight lg:text-6xl">
                        Smart Recycling for
                        <br />
                        <span className="text-emerald-600 dark:text-emerald-400">Beverage Brands</span>
                    </h1>
                    <p className="mx-auto mt-6 max-w-2xl text-lg text-gray-600 dark:text-gray-400">
                        Deploy AI-powered recycling bins at your outlets. Track waste, reward customers, and build brand loyalty through sustainable practices.
                    </p>
                    <div className="mt-10 flex items-center justify-center gap-4">
                        <a
                            href="#pricing"
                            className="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700"
                        >
                            View Plans
                        </a>
                        <a
                            href="#how-it-works"
                            className="rounded-lg border border-gray-300 px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900"
                        >
                            How It Works
                        </a>
                    </div>
                </section>

                {/* How It Works */}
                <section id="how-it-works" className="border-t border-gray-200 bg-gray-50 py-20 dark:border-gray-800 dark:bg-gray-900">
                    <div className="mx-auto max-w-6xl px-6">
                        <h2 className="mb-12 text-center text-3xl font-bold">How It Works</h2>
                        <div className="grid gap-8 md:grid-cols-3">
                            <div className="rounded-xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-gray-950">
                                <div className="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-lg font-bold text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300">1</div>
                                <h3 className="mb-2 text-lg font-semibold">Subscribe & Deploy</h3>
                                <p className="text-sm text-gray-600 dark:text-gray-400">Choose a plan, get smart bins installed at your outlets. We handle setup, pairing, and calibration.</p>
                            </div>
                            <div className="rounded-xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-gray-950">
                                <div className="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-lg font-bold text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300">2</div>
                                <h3 className="mb-2 text-lg font-semibold">AI Detects & Rewards</h3>
                                <p className="text-sm text-gray-600 dark:text-gray-400">Computer vision identifies waste types and brands. Customers earn points for proper recycling.</p>
                            </div>
                            <div className="rounded-xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-gray-950">
                                <div className="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-lg font-bold text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300">3</div>
                                <h3 className="mb-2 text-lg font-semibold">Track & Optimize</h3>
                                <p className="text-sm text-gray-600 dark:text-gray-400">Real-time analytics on recycling behavior. Route-optimized collection keeps bins serviced efficiently.</p>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Pricing */}
                <section id="pricing" className="border-t border-gray-200 py-20 dark:border-gray-800">
                    <div className="mx-auto max-w-6xl px-6">
                        <h2 className="mb-4 text-center text-3xl font-bold">Plans & Pricing</h2>
                        <p className="mb-12 text-center text-gray-600 dark:text-gray-400">
                            Choose the right plan for your brand.
                        </p>
                        <div className="grid gap-8 md:grid-cols-3">
                            {plans.map((plan) => {
                                const monthly = parseFloat(plan.price_monthly);
                                const isCustom = monthly === 0;

                                return (
                                    <div
                                        key={plan.id}
                                        className={`flex flex-col rounded-2xl border p-8 ${
                                            isCustom
                                                ? 'border-dashed border-gray-300 dark:border-gray-700'
                                                : 'border-gray-200 dark:border-gray-800'
                                        }`}
                                    >
                                        <h3 className="text-xl font-bold">{plan.name}</h3>
                                        <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">{plan.description}</p>
                                        <div className="mt-6">
                                            {isCustom ? (
                                                <span className="text-4xl font-bold">Let's Talk</span>
                                            ) : (
                                                <div className="flex items-baseline gap-1">
                                                    <span className="text-4xl font-bold">RM{monthly}</span>
                                                    <span className="text-sm text-gray-500">/mo</span>
                                                </div>
                                            )}
                                        </div>
                                        {/* Features list — to be designed */}
                                        <div className="mt-8 flex-1" />
                                        <button
                                            type="button"
                                            className="mt-8 block w-full cursor-default rounded-lg border border-gray-300 py-3 text-center text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-300"
                                            disabled
                                        >
                                            {isCustom ? 'Contact Sales' : 'Get Started'}
                                        </button>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="border-t border-gray-200 py-12 dark:border-gray-800">
                    <div className="mx-auto flex max-w-6xl items-center justify-between px-6 text-sm text-gray-500 dark:text-gray-400">
                        <span>Mobius Smart Recycling Bin Ecosystem for Beverage Stores &copy; {new Date().getFullYear()}</span>
                        <Link
                            href={login()}
                            className="text-gray-400 hover:text-gray-600 dark:text-gray-600 dark:hover:text-gray-400"
                        >
                            Sign In
                        </Link>
                    </div>
                </footer>
            </div>
        </>
    );
}

import { Head, Link, useForm } from '@inertiajs/react';

type Plan = { id: number; name: string };

export default function GetStarted({
    plans,
    selectedPlanId,
}: {
    plans: Plan[];
    selectedPlanId?: string;
}) {
    const form = useForm({
        contact_name: '',
        contact_email: '',
        contact_phone: '',
        company_name: '',
        type: 'beverage_company' as string,
        selected_plan_id: selectedPlanId ?? '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.post('/get-started');
    }

    const selectedPlan = plans.find((p) => String(p.id) === String(form.data.selected_plan_id));

    return (
        <>
            <Head title="Get Started" />
            <div className="min-h-screen bg-white dark:bg-gray-950">
                <header className="border-b border-gray-200 dark:border-gray-800">
                    <div className="mx-auto flex max-w-2xl items-center justify-between px-6 py-4">
                        <Link href="/" className="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Mobius
                        </Link>
                        <Link href="/" className="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            Back to home
                        </Link>
                    </div>
                </header>

                <main className="mx-auto max-w-2xl px-6 py-12">
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Get Started with Mobius</h1>
                    <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Tell us about your business. We'll get back to you to discuss next steps.
                        {selectedPlan && (
                            <span className="ml-1 font-medium text-gray-900 dark:text-white">
                                Selected plan: {selectedPlan.name}
                            </span>
                        )}
                    </p>

                    <form onSubmit={submit} className="mt-8 space-y-6">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Your Name</label>
                            <input
                                type="text"
                                value={form.data.contact_name}
                                onChange={(e) => form.setData('contact_name', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                            {form.errors.contact_name && <p className="mt-1 text-xs text-red-600">{form.errors.contact_name}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input
                                type="email"
                                value={form.data.contact_email}
                                onChange={(e) => form.setData('contact_email', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                            {form.errors.contact_email && <p className="mt-1 text-xs text-red-600">{form.errors.contact_email}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                            <input
                                type="text"
                                value={form.data.contact_phone}
                                onChange={(e) => form.setData('contact_phone', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                            {form.errors.contact_phone && <p className="mt-1 text-xs text-red-600">{form.errors.contact_phone}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Business Name</label>
                            <input
                                type="text"
                                value={form.data.company_name}
                                onChange={(e) => form.setData('company_name', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            />
                            {form.errors.company_name && <p className="mt-1 text-xs text-red-600">{form.errors.company_name}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Business Type</label>
                            <select
                                value={form.data.type}
                                onChange={(e) => form.setData('type', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                                <option value="beverage_company">Beverage Company</option>
                                <option value="recycling_company">Recycling Company</option>
                                <option value="government">Government</option>
                            </select>
                            {form.errors.type && <p className="mt-1 text-xs text-red-600">{form.errors.type}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Selected Plan</label>
                            <select
                                value={form.data.selected_plan_id}
                                onChange={(e) => form.setData('selected_plan_id', e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                                <option value="">Not sure yet</option>
                                {plans.map((plan) => (
                                    <option key={plan.id} value={plan.id}>{plan.name}</option>
                                ))}
                            </select>
                        </div>

                        <button
                            type="submit"
                            disabled={form.processing}
                            className="w-full rounded-lg bg-emerald-600 py-3 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {form.processing ? 'Submitting...' : 'Submit Inquiry'}
                        </button>
                    </form>
                </main>
            </div>
        </>
    );
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Stripe\StripeClient;

class BillingController extends Controller
{
    public function index(): Response
    {
        $subscriptions = Subscription::query()
            ->with(['organization:id,name', 'plan:id,name,price_monthly'])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->through(fn (Subscription $sub) => [
                'id' => $sub->id,
                'organization' => $sub->organization->name,
                'plan' => $sub->plan->name,
                'price_monthly' => $sub->custom_price_monthly ?? $sub->plan->price_monthly,
                'billing_interval' => $sub->billing_interval,
                'status' => $sub->status,
                'has_overrides' => $sub->custom_bin_limit !== null || $sub->custom_outlet_limit !== null,
                'has_stripe_price' => (bool) $sub->stripe_price_id,
                'starts_at' => $sub->starts_at?->format('Y-m-d'),
                'ends_at' => $sub->ends_at?->format('Y-m-d'),
                'created_at' => $sub->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Billing', [
            'subscriptions' => $subscriptions,
        ]);
    }

    public function activate(Subscription $subscription): RedirectResponse
    {
        if ($subscription->status !== 'pending_payment') {
            return back();
        }

        $subscription->update([
            'status' => 'active',
            'starts_at' => now(),
        ]);

        return redirect()->route('admin.billing');
    }

    public function customize(Subscription $subscription): Response
    {
        $subscription->load(['organization:id,name', 'plan:id,name,bin_limit,outlet_limit,staff_limit']);

        return Inertia::render('Admin/Billing/Customize', [
            'subscription' => [
                'id' => $subscription->id,
                'organization' => $subscription->organization->name,
                'plan' => $subscription->plan->name,
                'plan_bin_limit' => $subscription->plan->bin_limit,
                'plan_outlet_limit' => $subscription->plan->outlet_limit,
                'plan_staff_limit' => $subscription->plan->staff_limit,
                'custom_bin_limit' => $subscription->custom_bin_limit,
                'custom_outlet_limit' => $subscription->custom_outlet_limit,
                'custom_staff_limit' => $subscription->custom_staff_limit,
                'custom_price_monthly' => $subscription->custom_price_monthly,
                'billing_interval' => $subscription->billing_interval,
                'notes' => $subscription->notes,
                'status' => $subscription->status,
                'stripe_price_id' => $subscription->stripe_price_id,
            ],
        ]);
    }

    public function updateCustom(Request $request, Subscription $subscription): RedirectResponse
    {
        $validated = $request->validate([
            'custom_bin_limit' => ['nullable', 'integer', 'min:1'],
            'custom_outlet_limit' => ['nullable', 'integer', 'min:1'],
            'custom_staff_limit' => ['nullable', 'integer', 'min:1'],
            'custom_price_monthly' => ['nullable', 'numeric', 'min:0'],
            'billing_interval' => ['required', 'in:monthly,yearly'],
            'notes' => ['nullable', 'string'],
            'create_stripe_price' => ['nullable', 'boolean'],
        ]);

        $createStripePrice = $validated['create_stripe_price'] ?? false;
        unset($validated['create_stripe_price']);

        $subscription->update($validated);

        // Create Stripe Price if requested and custom price is set
        if ($createStripePrice && $subscription->custom_price_monthly > 0) {
            $stripePriceId = $this->createStripePrice($subscription);
            if ($stripePriceId) {
                $subscription->update(['stripe_price_id' => $stripePriceId]);
            }
        }

        return redirect()->route('admin.billing');
    }

    private function createStripePrice(Subscription $subscription): ?string
    {
        try {
            $stripe = new StripeClient(config('cashier.secret'));
            $subscription->load('organization');

            // Find or create the "Mobius Custom Plan" product
            $productId = $this->getOrCreateCustomProduct($stripe);

            // Calculate amount in cents (MYR)
            $interval = $subscription->billing_interval;
            $amountMonthly = (int) round($subscription->custom_price_monthly * 100);

            if ($interval === 'yearly') {
                // Yearly: multiply monthly by 12
                $amount = $amountMonthly * 12;
            } else {
                $amount = $amountMonthly;
            }

            $price = $stripe->prices->create([
                'product' => $productId,
                'unit_amount' => $amount,
                'currency' => 'myr',
                'recurring' => [
                    'interval' => $interval === 'yearly' ? 'year' : 'month',
                ],
                'nickname' => 'Custom — '.$subscription->organization->name,
            ]);

            return $price->id;
        } catch (\Exception $e) {
            report($e);

            return null;
        }
    }

    private function getOrCreateCustomProduct(StripeClient $stripe): string
    {
        // Search for existing product by metadata
        $products = $stripe->products->search([
            'query' => "metadata['mobius_type']:'custom_plan'",
        ]);

        if (count($products->data) > 0) {
            return $products->data[0]->id;
        }

        // Create it
        $product = $stripe->products->create([
            'name' => 'Mobius Custom Plan',
            'description' => 'Custom enterprise plan for Mobius Smart Recycling Bin Ecosystem',
            'metadata' => ['mobius_type' => 'custom_plan'],
        ]);

        return $product->id;
    }
}

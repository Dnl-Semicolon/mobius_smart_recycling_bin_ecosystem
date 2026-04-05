<?php

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Checkout;
use Stripe\StripeClient;

class BillingController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $org = $user->organization;
        $orgSub = $org ? Subscription::where('organization_id', $org->id)->with('plan')->first() : null;

        // If user has a stripe_id but Cashier doesn't know about their subscription, sync it
        $this->syncStripeSubscriptionIfMissing($user);

        $stripeData = null;
        $invoices = [];
        $isSubscribedViaCashier = $user->subscribed('default');

        if ($isSubscribedViaCashier) {
            $stripeSub = $user->subscription('default');

            $stripeData = [
                'stripe_status' => $stripeSub->stripe_status,
                'current_period_end' => $stripeSub->ends_at?->format('Y-m-d'),
            ];

            // Payment method
            try {
                if ($user->hasDefaultPaymentMethod()) {
                    $pm = $user->defaultPaymentMethod();
                    $stripeData['pm_brand'] = $pm->card?->brand ?? $user->pm_type;
                    $stripeData['pm_last_four'] = $pm->card?->last4 ?? $user->pm_last_four;
                }
            } catch (\Exception) {
            }

            // Upcoming invoice
            try {
                $upcoming = $user->upcomingInvoice();
                if ($upcoming) {
                    $stripeData['next_payment_date'] = date('Y-m-d', $upcoming->next_payment_attempt ?? $upcoming->created);
                    $stripeData['next_payment_amount'] = number_format($upcoming->total / 100, 2);
                }
            } catch (\Exception) {
            }

            // Recent invoices + receipts
            try {
                $invoices = $user->invoices()->map(function ($invoice) {
                    $stripeInvoice = $invoice->asStripeInvoice();

                    return [
                        'id' => $invoice->id,
                        'date' => $invoice->date()->format('Y-m-d'),
                        'amount' => $invoice->total(),
                        'status' => $stripeInvoice->status ?? 'paid',
                        'pdf_url' => $stripeInvoice->invoice_pdf,
                        'receipt_url' => $stripeInvoice->hosted_invoice_url,
                    ];
                })->take(10)->toArray();
            } catch (\Exception) {
                $invoices = [];
            }
        }

        // Determine effective price and stripe_price_id
        $effectiveStripePriceId = $orgSub?->stripe_price_id ?? $orgSub?->plan?->stripe_price_id;
        $effectivePrice = $orgSub?->custom_price_monthly ?? $orgSub?->plan?->price_monthly;
        $billingInterval = $orgSub?->billing_interval ?? 'monthly';
        $isCustom = $orgSub?->custom_price_monthly !== null;

        // Effective limits for display
        $limits = $org ? [
            'bins' => $org->getLimitInfo('bin_limit'),
            'outlets' => $org->getLimitInfo('outlet_limit'),
            'staff' => $org->getLimitInfo('staff_limit'),
        ] : null;

        return Inertia::render('Brand/Billing', [
            'subscription' => $orgSub ? [
                'plan_name' => $orgSub->plan->name,
                'price' => $effectivePrice,
                'billing_interval' => $billingInterval,
                'stripe_price_id' => $effectiveStripePriceId,
                'is_custom' => $isCustom,
                'status' => $orgSub->status,
                'starts_at' => $orgSub->starts_at?->format('Y-m-d'),
                'ends_at' => $orgSub->ends_at?->format('Y-m-d'),
            ] : null,
            'limits' => $limits,
            'stripe' => $stripeData,
            'invoices' => $invoices,
            'hasStripeSubscription' => $user->fresh()->subscribed('default'),
        ]);
    }

    public function checkout(): Checkout|RedirectResponse
    {
        $user = auth()->user();
        $org = $user->organization;
        $orgSub = Subscription::where('organization_id', $org->id)->with('plan')->first();

        // Use org-specific price if set, otherwise plan price
        $stripePriceId = $orgSub?->stripe_price_id ?? $orgSub?->plan?->stripe_price_id;

        if (! $stripePriceId) {
            return back();
        }

        return $user->newSubscription('default', $stripePriceId)
            ->checkout([
                'success_url' => route('brand.billing.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('brand.billing.cancel'),
            ]);
    }

    public function success(): Response
    {
        $user = auth()->user();
        $org = $user->organization;

        // Sync Stripe subscription to Cashier table (fallback if webhook didn't fire)
        $this->syncStripeSubscriptionIfMissing($user);

        // Activate our org subscription
        if ($org) {
            $orgSub = Subscription::where('organization_id', $org->id)->first();
            if ($orgSub && $orgSub->status === 'pending_payment') {
                $orgSub->update([
                    'status' => 'active',
                    'starts_at' => now(),
                ]);
            }
        }

        return Inertia::render('Brand/BillingSuccess');
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('brand.billing');
    }

    public function portal(): RedirectResponse
    {
        return auth()->user()->redirectToBillingPortal(route('brand.billing'));
    }

    /**
     * If the user has a Stripe customer but no Cashier subscription record,
     * query Stripe directly and create the local record.
     */
    private function syncStripeSubscriptionIfMissing($user): void
    {
        if (! $user->stripe_id || $user->subscribed('default')) {
            return;
        }

        try {
            $stripe = new StripeClient(config('cashier.secret'));
            $stripeSubs = $stripe->subscriptions->all([
                'customer' => $user->stripe_id,
                'status' => 'active',
                'limit' => 1,
            ]);

            if (count($stripeSubs->data) === 0) {
                return;
            }

            $sub = $stripeSubs->data[0];
            $item = $sub->items->data[0];

            DB::table('subscriptions')->insert([
                'user_id' => $user->id,
                'type' => 'default',
                'stripe_id' => $sub->id,
                'stripe_status' => $sub->status,
                'stripe_price' => $item->price->id,
                'quantity' => $item->quantity ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('subscription_items')->insert([
                'subscription_id' => DB::getPdo()->lastInsertId(),
                'stripe_id' => $item->id,
                'stripe_product' => $item->price->product,
                'stripe_price' => $item->price->id,
                'quantity' => $item->quantity ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update user payment method info
            $user->update([
                'pm_type' => $sub->default_payment_method ? 'card' : null,
            ]);
        } catch (\Exception) {
            // Stripe API call failed — not critical, page still renders
        }
    }
}

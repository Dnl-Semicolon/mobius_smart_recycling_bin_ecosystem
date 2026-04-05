<?php

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $org = $user->organization;
        $orgSub = $org ? Subscription::where('organization_id', $org->id)->with('plan')->first() : null;

        return Inertia::render('Brand/Billing', [
            'subscription' => $orgSub ? [
                'plan_name' => $orgSub->plan->name,
                'price_monthly' => $orgSub->plan->price_monthly,
                'stripe_price_id' => $orgSub->plan->stripe_price_id,
                'status' => $orgSub->status,
                'starts_at' => $orgSub->starts_at->format('Y-m-d'),
                'ends_at' => $orgSub->ends_at->format('Y-m-d'),
            ] : null,
            'hasStripeSubscription' => $user->subscribed('default'),
            'stripeKey' => config('cashier.key'),
        ]);
    }

    public function checkout(): RedirectResponse
    {
        $user = auth()->user();
        $org = $user->organization;
        $orgSub = Subscription::where('organization_id', $org->id)->with('plan')->first();

        if (! $orgSub?->plan?->stripe_price_id) {
            return back();
        }

        $checkout = $user->newSubscription('default', $orgSub->plan->stripe_price_id)
            ->checkout([
                'success_url' => route('brand.billing.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('brand.billing.cancel'),
            ]);

        return $checkout;
    }

    public function success(): Response
    {
        return Inertia::render('Brand/BillingSuccess');
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('brand.billing')->with('status', 'Payment cancelled.');
    }

    public function portal(): RedirectResponse
    {
        return auth()->user()->redirectToBillingPortal(route('brand.billing'));
    }
}

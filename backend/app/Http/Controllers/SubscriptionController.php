<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function plans(): View
    {
        return view('subscribe.plans');
    }

    public function checkout(Request $request): mixed
    {
        $request->validate([
            'price' => ['required', 'in:basic,premium'],
        ]);

        $priceId = config("services.stripe.prices.{$request->price}");

        if (! $priceId) {
            return back()->with('error', 'Stripe price not configured. Set STRIPE_PRICE_BASIC and STRIPE_PRICE_PREMIUM in .env.');
        }

        return $request->user()->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => route('subscribe.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('subscribe.cancel'),
            ]);
    }

    public function success(): View
    {
        return view('subscribe.success');
    }

    public function cancel(): View
    {
        return view('subscribe.cancel');
    }

    public function manage(Request $request): View
    {
        return view('subscribe.manage', [
            'user' => $request->user(),
            'subscription' => $request->user()->subscription('default'),
        ]);
    }

    public function cancelSubscription(Request $request): RedirectResponse
    {
        $subscription = $request->user()->subscription('default');

        if ($subscription && ! $subscription->canceled()) {
            $subscription->cancel();

            return back()->with('success', 'Subscription will be cancelled at the end of the billing period.');
        }

        return back()->with('error', 'No active subscription to cancel.');
    }

    public function billingPortal(Request $request): mixed
    {
        return $request->user()->redirectToBillingPortal(route('subscribe.manage'));
    }
}

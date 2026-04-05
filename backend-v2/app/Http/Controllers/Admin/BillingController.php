<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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
        $subscription->load(['organization:id,name', 'plan:id,name']);

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
        ]);

        $subscription->update($validated);

        return redirect()->route('admin.billing');
    }
}

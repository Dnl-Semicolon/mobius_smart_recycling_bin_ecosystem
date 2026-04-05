<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
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
                'price_monthly' => $sub->plan->price_monthly,
                'status' => $sub->status,
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
            'ends_at' => now()->addYear(),
            'renews_at' => now()->addYear()->subMonth(),
        ]);

        return redirect()->route('admin.billing');
    }
}

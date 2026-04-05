<?php

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $org = $user->organization;

        $brand = $org?->brands()->first();
        $subscription = $org?->subscription;
        $subscription?->load('plan:id,name,price_monthly');
        $outlets = $org ? $org->brands()->with('outlets')->get()->flatMap->outlets : collect();

        return Inertia::render('Brand/Dashboard', [
            'organization' => $org ? [
                'id' => $org->id,
                'name' => $org->name,
                'type' => $org->type,
            ] : null,
            'brand' => $brand ? [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
            ] : null,
            'subscription' => $subscription ? [
                'plan' => $subscription->plan->name,
                'price_monthly' => $subscription->plan->price_monthly,
                'status' => $subscription->status,
                'starts_at' => $subscription->starts_at->format('Y-m-d'),
                'ends_at' => $subscription->ends_at->format('Y-m-d'),
                'renews_at' => $subscription->renews_at->format('Y-m-d'),
            ] : null,
            'outlets' => $outlets->map(fn ($o) => [
                'id' => $o->id,
                'name' => $o->name,
                'address' => $o->address,
                'is_active' => $o->is_active,
            ])->values(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(): JsonResponse
    {
        $subscriptions = Subscription::query()
            ->with(['organization', 'plan'])
            ->latest()
            ->paginate(20);

        return response()->json($subscriptions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'renews_at' => ['required', 'date'],
        ]);

        $subscription = Subscription::create($validated);
        $subscription->load(['organization', 'plan']);

        return response()->json(['data' => $subscription, 'message' => 'Subscription created successfully.'], 201);
    }

    public function show(Subscription $subscription): JsonResponse
    {
        $subscription->load(['organization', 'plan', 'payments']);

        return response()->json(['data' => $subscription]);
    }
}

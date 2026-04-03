<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = Plan::query()
            ->where('is_active', true)
            ->get();

        return response()->json(['data' => $plans]);
    }

    public function show(Plan $plan): JsonResponse
    {
        return response()->json(['data' => $plan]);
    }
}

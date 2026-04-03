<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index(): JsonResponse
    {
        $organizations = Organization::query()
            ->with(['brands', 'subscription.plan'])
            ->latest()
            ->paginate(20);

        return response()->json($organizations);
    }

    public function show(Organization $organization): JsonResponse
    {
        $organization->load(['brands', 'users', 'subscription.plan']);

        return response()->json(['data' => $organization]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:beverage_company,recycling_company,government'],
            'description' => ['required', 'string'],
            'logo_path' => ['sometimes', 'string', 'max:255'],
            'website' => ['sometimes', 'string', 'max:255'],
        ]);

        $organization = Organization::create($validated);

        return response()->json(['data' => $organization, 'message' => 'Organization created successfully.'], 201);
    }

    public function update(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'in:beverage_company,recycling_company,government'],
            'description' => ['sometimes', 'string'],
            'logo_path' => ['sometimes', 'string', 'max:255'],
            'website' => ['sometimes', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $organization->update($validated);

        return response()->json(['data' => $organization, 'message' => 'Organization updated successfully.']);
    }
}

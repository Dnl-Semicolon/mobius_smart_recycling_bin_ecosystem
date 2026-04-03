<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RegistrationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistrationRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $registrationRequests = RegistrationRequest::query()
            ->with('reviewer')
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->latest()
            ->paginate(20);

        return response()->json($registrationRequests);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:20'],
            'type' => ['required', 'string', 'in:beverage_company,recycling_company,government'],
            'description' => ['required', 'string'],
        ]);

        $validated['status'] = 'pending';
        $validated['admin_notes'] = '';

        $registrationRequest = RegistrationRequest::create($validated);

        return response()->json(['data' => $registrationRequest, 'message' => 'Registration request submitted successfully.'], 201);
    }

    public function approve(Request $request, RegistrationRequest $registrationRequest): JsonResponse
    {
        $registrationRequest->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_notes' => $request->input('admin_notes', $registrationRequest->admin_notes),
        ]);

        $registrationRequest->load('reviewer');

        return response()->json(['data' => $registrationRequest, 'message' => 'Registration request approved.']);
    }

    public function reject(Request $request, RegistrationRequest $registrationRequest): JsonResponse
    {
        $registrationRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_notes' => $request->input('admin_notes', $registrationRequest->admin_notes),
        ]);

        $registrationRequest->load('reviewer');

        return response()->json(['data' => $registrationRequest, 'message' => 'Registration request rejected.']);
    }
}

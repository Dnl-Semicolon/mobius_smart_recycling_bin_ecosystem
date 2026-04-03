<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::query()
            ->with(['organization', 'subscription'])
            ->when($request->filled('organization_id'), function ($query) use ($request) {
                $query->where('organization_id', $request->input('organization_id'));
            })
            ->latest()
            ->paginate(20);

        return response()->json($payments);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'subscription_id' => ['required', 'integer', 'exists:subscriptions,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'string', 'in:card,bank_transfer,manual'],
            'reference_number' => ['required', 'string', 'max:255'],
            'paid_at' => ['required', 'date'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'status' => ['sometimes', 'string', 'in:pending,completed,failed,refunded'],
        ]);

        $payment = Payment::create($validated);
        $payment->load(['organization', 'subscription']);

        return response()->json(['data' => $payment, 'message' => 'Payment recorded successfully.'], 201);
    }
}

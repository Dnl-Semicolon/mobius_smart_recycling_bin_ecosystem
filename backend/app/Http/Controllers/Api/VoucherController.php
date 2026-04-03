<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RecyclingTransaction;
use App\Models\VoucherAllocation;
use App\Models\VoucherClaim;
use App\Models\VoucherTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    public function templates(Request $request): JsonResponse
    {
        $templates = VoucherTemplate::query()
            ->with('brand')
            ->when($request->filled('brand_id'), function ($query) use ($request) {
                $query->where('brand_id', $request->input('brand_id'));
            })
            ->latest()
            ->paginate(20);

        return response()->json($templates);
    }

    public function storeTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', 'string', 'in:discount,free_item,cashback'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'points_required' => ['required', 'integer', 'min:1'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after:valid_from'],
        ]);

        $template = VoucherTemplate::create($validated);
        $template->load('brand');

        return response()->json(['data' => $template, 'message' => 'Voucher template created successfully.'], 201);
    }

    public function allocations(VoucherTemplate $template): JsonResponse
    {
        $allocations = $template->allocations()->with('outlet')->get();

        return response()->json(['data' => $allocations]);
    }

    public function storeAllocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'voucher_template_id' => ['required', 'integer', 'exists:voucher_templates,id'],
            'outlet_id' => ['required', 'integer', 'exists:outlets,id'],
            'quota' => ['required', 'integer', 'min:1'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after:valid_from'],
        ]);

        $validated['claimed_count'] = 0;

        $allocation = VoucherAllocation::create($validated);
        $allocation->load(['voucherTemplate', 'outlet']);

        return response()->json(['data' => $allocation, 'message' => 'Voucher allocation created successfully.'], 201);
    }

    public function claim(VoucherTemplate $template, Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $template->isCurrentlyValid()) {
            return response()->json(['message' => 'This voucher template is no longer valid.'], 422);
        }

        if ($user->points_balance < $template->points_required) {
            return response()->json(['message' => 'Insufficient points. You need '.$template->points_required.' points.'], 422);
        }

        $validated = $request->validate([
            'voucher_allocation_id' => ['required', 'integer', 'exists:voucher_allocations,id'],
        ]);

        $allocation = VoucherAllocation::findOrFail($validated['voucher_allocation_id']);

        if (! $allocation->hasQuotaRemaining()) {
            return response()->json(['message' => 'This voucher allocation has no remaining quota.'], 422);
        }

        $claim = DB::transaction(function () use ($user, $template, $allocation) {
            $claim = VoucherClaim::create([
                'voucher_template_id' => $template->id,
                'voucher_allocation_id' => $allocation->id,
                'user_id' => $user->id,
                'points_spent' => $template->points_required,
                'status' => 'claimed',
                'claimed_at' => now(),
                'expires_at' => $template->valid_until,
            ]);

            $user->decrement('points_balance', $template->points_required);

            $allocation->increment('claimed_count');

            RecyclingTransaction::create([
                'user_id' => $user->id,
                'bin_session_id' => null,
                'type' => 'spent',
                'points' => -$template->points_required,
                'description' => 'Voucher claimed: '.$template->name,
            ]);

            return $claim;
        });

        $claim->load('voucherTemplate.brand');

        return response()->json(['data' => $claim, 'message' => 'Voucher claimed successfully.'], 201);
    }

    public function myClaims(Request $request): JsonResponse
    {
        $claims = VoucherClaim::query()
            ->where('user_id', $request->user()->id)
            ->with('voucherTemplate.brand')
            ->latest()
            ->paginate(20);

        return response()->json($claims);
    }

    public function redeem(VoucherClaim $claim): JsonResponse
    {
        if ($claim->isRedeemed()) {
            return response()->json(['message' => 'This voucher has already been redeemed.'], 422);
        }

        if ($claim->isExpired()) {
            return response()->json(['message' => 'This voucher has expired.'], 422);
        }

        $claim->update([
            'status' => 'redeemed',
            'redeemed_at' => now(),
        ]);

        $claim->load('voucherTemplate.brand');

        return response()->json(['data' => $claim, 'message' => 'Voucher redeemed successfully.']);
    }
}

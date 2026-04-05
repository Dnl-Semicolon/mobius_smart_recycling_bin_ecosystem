<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\VoucherClaim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RedemptionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Store/Redeem');
    }

    public function lookup(Request $request): Response
    {
        $validated = $request->validate([
            'qr_code' => ['required', 'string'],
        ]);

        $claim = VoucherClaim::where('qr_code', $validated['qr_code'])
            ->with(['voucherTemplate.brand:id,name', 'user:id,name,email'])
            ->first();

        if (! $claim) {
            return Inertia::render('Store/Redeem', [
                'error' => 'Voucher not found. Invalid QR code.',
            ]);
        }

        return Inertia::render('Store/Redeem', [
            'claim' => [
                'id' => $claim->id,
                'qr_code' => $claim->qr_code,
                'voucher_name' => $claim->voucherTemplate->name,
                'voucher_value' => $claim->voucherTemplate->value,
                'voucher_type' => $claim->voucherTemplate->type,
                'brand' => $claim->voucherTemplate->brand->name,
                'user_name' => $claim->user->name,
                'status' => $claim->status,
                'claimed_at' => $claim->claimed_at->format('Y-m-d H:i'),
                'redeemed_at' => $claim->redeemed_at?->format('Y-m-d H:i'),
            ],
        ]);
    }

    public function redeem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'claim_id' => ['required', 'exists:voucher_claims,id'],
        ]);

        $claim = VoucherClaim::findOrFail($validated['claim_id']);

        if ($claim->status !== 'claimed') {
            return back()->withErrors(['redeem' => 'This voucher has already been '.$claim->status.'.']);
        }

        $claim->update([
            'status' => 'redeemed',
            'redeemed_at' => now(),
        ]);

        return redirect()->route('store.redeem')->with('success', 'Voucher redeemed successfully!');
    }
}

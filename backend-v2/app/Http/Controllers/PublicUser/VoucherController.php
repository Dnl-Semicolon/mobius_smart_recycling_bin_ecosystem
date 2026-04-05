<?php

namespace App\Http\Controllers\PublicUser;

use App\Http\Controllers\Controller;
use App\Models\RecyclingTransaction;
use App\Models\VoucherClaim;
use App\Models\VoucherTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class VoucherController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $vouchers = VoucherTemplate::query()
            ->with('brand:id,name')
            ->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (VoucherTemplate $v) => [
                'id' => $v->id,
                'name' => $v->name,
                'description' => $v->description,
                'brand' => $v->brand->name,
                'type' => $v->type,
                'value' => $v->value,
                'points_required' => $v->points_required,
                'valid_until' => $v->valid_until->format('Y-m-d'),
                'can_afford' => $user->points_balance >= $v->points_required,
                'is_promo' => $v->isPromo(),
                'remaining' => $v->remainingQuota(),
                'sold_out' => $v->isPromo() && ! $v->hasQuotaRemaining(),
            ]);

        $myClaims = VoucherClaim::where('user_id', $user->id)
            ->with('voucherTemplate:id,name,value,type')
            ->orderByDesc('claimed_at')
            ->limit(20)
            ->get()
            ->map(fn (VoucherClaim $c) => [
                'id' => $c->id,
                'voucher_name' => $c->voucherTemplate->name,
                'voucher_value' => $c->voucherTemplate->value,
                'points_spent' => $c->points_spent,
                'status' => $c->status,
                'qr_code' => $c->qr_code,
                'claimed_at' => $c->claimed_at->format('Y-m-d H:i'),
                'redeemed_at' => $c->redeemed_at?->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Public/Vouchers', [
            'vouchers' => $vouchers,
            'myClaims' => $myClaims,
            'pointsBalance' => $user->points_balance,
        ]);
    }

    public function claim(VoucherTemplate $template): RedirectResponse
    {
        $user = auth()->user();

        if (! $template->isCurrentlyValid()) {
            return back()->withErrors(['voucher' => 'This voucher is no longer available.']);
        }

        if ($user->points_balance < $template->points_required) {
            return back()->withErrors(['voucher' => 'Not enough points. You need '.$template->points_required.' points.']);
        }

        if ($template->isPromo() && ! $template->hasQuotaRemaining()) {
            return back()->withErrors(['voucher' => 'This promotional voucher is sold out.']);
        }

        DB::transaction(function () use ($user, $template) {
            // Deduct points
            $user->decrement('points_balance', $template->points_required);

            // Increment claimed count (for promo vouchers)
            if ($template->isPromo()) {
                $template->increment('claimed_count');
            }

            // Create transaction record
            RecyclingTransaction::create([
                'user_id' => $user->id,
                'type' => 'spent',
                'points' => -$template->points_required,
                'description' => 'Redeemed voucher: '.$template->name,
            ]);

            // Create claim
            VoucherClaim::create([
                'voucher_template_id' => $template->id,
                'user_id' => $user->id,
                'points_spent' => $template->points_required,
                'status' => 'claimed',
                'claimed_at' => now(),
                'expires_at' => $template->valid_until,
            ]);
        });

        return redirect()->route('public.vouchers');
    }
}

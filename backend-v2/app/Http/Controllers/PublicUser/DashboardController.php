<?php

namespace App\Http\Controllers\PublicUser;

use App\Http\Controllers\Controller;
use App\Models\RecyclingTransaction;
use App\Models\VoucherClaim;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function qrCode(): Response
    {
        $user = auth()->user();

        // Encode user ID as JSON — the bin-client parses this and calls link-user API
        $qrValue = json_encode(['uid' => $user->id]);

        return Inertia::render('Public/QrCode', [
            'qrValue' => $qrValue,
            'userName' => $user->name,
        ]);
    }

    public function index(): Response
    {
        $user = auth()->user();

        $recentTransactions = RecyclingTransaction::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['type', 'points', 'description', 'created_at'])
            ->map(fn ($t) => [
                'type' => $t->type,
                'points' => $t->points,
                'description' => $t->description,
                'date' => $t->created_at->format('Y-m-d H:i'),
            ]);

        $claimedVouchers = VoucherClaim::where('user_id', $user->id)->count();

        return Inertia::render('Public/Dashboard', [
            'stats' => [
                'points_balance' => $user->points_balance,
                'current_streak' => $user->current_streak,
                'longest_streak' => $user->longest_streak,
                'claimed_vouchers' => $claimedVouchers,
            ],
            'recentTransactions' => $recentTransactions,
        ]);
    }
}

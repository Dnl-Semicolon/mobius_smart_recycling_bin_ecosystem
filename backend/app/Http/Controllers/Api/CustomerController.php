<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bin;
use App\Models\BinSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Get the authenticated user's recycling stats.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'points_balance' => $user->points_balance,
            'current_streak' => $user->current_streak,
            'longest_streak' => $user->longest_streak,
            'last_recycled_at' => $user->last_recycled_at?->toIso8601String(),
        ]);
    }

    /**
     * Get the authenticated user's recycling history (paginated).
     */
    public function history(Request $request): JsonResponse
    {
        $transactions = $request->user()
            ->recyclingTransactions()
            ->with('binSession')
            ->latest()
            ->paginate(15);

        return response()->json($transactions);
    }

    /**
     * Get the top 10 users by points_balance.
     */
    public function leaderboard(): JsonResponse
    {
        $leaders = User::query()
            ->where('points_balance', '>', 0)
            ->orderByDesc('points_balance')
            ->limit(10)
            ->get(['id', 'name', 'points_balance', 'current_streak', 'longest_streak'])
            ->map(fn (User $user, int $index) => [
                'rank' => $index + 1,
                'name' => $user->name,
                'points' => $user->points_balance,
                'current_streak' => $user->current_streak,
                'longest_streak' => $user->longest_streak,
            ]);

        return response()->json(['data' => $leaders]);
    }

    /**
     * Start a session at a bin. The user scanned the bin's QR code.
     */
    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bin_serial' => ['required', 'string'],
        ]);

        $bin = Bin::where('serial_number', $validated['bin_serial'])->first();

        if (! $bin) {
            return response()->json([
                'data' => null,
                'message' => 'Bin not found.',
            ], 404);
        }

        $user = $request->user();

        $session = BinSession::create([
            'bin_id' => $bin->id,
            'user_id' => $user->id,
            'status' => 'active',
            'started_at' => now(),
        ]);

        $bin->load('outlet.brand');

        return response()->json([
            'data' => [
                'session_id' => $session->id,
                'bin_id' => $bin->id,
                'bin_serial' => $bin->serial_number,
                'outlet_name' => $bin->outlet?->name,
                'brand_name' => $bin->outlet?->brand?->name,
            ],
            'message' => 'Session started. Deposit your item now!',
        ]);
    }
}

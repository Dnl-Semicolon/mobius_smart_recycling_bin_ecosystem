<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bin;
use App\Models\BinSession;
use App\Models\RecyclingTransaction;
use App\Models\User;
use App\Services\DetectionPipelineService;
use App\Services\PointsCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DetectionPipelineController extends Controller
{
    public function listBins(): JsonResponse
    {
        $bins = Bin::with('outlet.brand')
            ->where('status', 'active')
            ->get()
            ->map(fn ($bin) => [
                'serial_number' => $bin->serial_number,
                'fill_level' => $bin->fill_level,
                'weight_grams' => $bin->weight_grams,
                'outlet' => $bin->outlet?->name,
                'brand' => $bin->outlet?->brand?->name,
                'address' => $bin->outlet?->address,
            ]);

        return response()->json(['bins' => $bins]);
    }

    public function startSession(string $serial): JsonResponse
    {
        $bin = Bin::where('serial_number', $serial)->where('status', 'active')->first();

        if (! $bin) {
            return response()->json(['error' => 'Bin not found or inactive'], 404);
        }

        $session = BinSession::create([
            'bin_id' => $bin->id,
            'status' => 'active',
            'cup_rinsed' => false,
            'started_at' => now(),
        ]);

        return response()->json([
            'session_id' => $session->id,
            'bin' => [
                'serial_number' => $bin->serial_number,
                'fill_level' => $bin->fill_level,
                'outlet' => $bin->outlet?->name,
                'brand' => $bin->outlet?->brand?->name,
            ],
        ], 201);
    }

    public function linkUser(string $serial, BinSession $session, Request $request): JsonResponse
    {
        $bin = Bin::where('serial_number', $serial)->where('status', 'active')->firstOrFail();

        if ($session->bin_id !== $bin->id) {
            return response()->json(['error' => 'Session does not belong to this bin'], 403);
        }

        $request->validate(['user_id' => 'required|exists:users,id']);

        $session->update(['user_id' => $request->user_id]);

        $user = User::find($request->user_id);

        return response()->json([
            'message' => 'User linked to session',
            'user' => [
                'name' => $user->name,
                'points_balance' => $user->points_balance,
            ],
        ]);
    }

    public function detect(string $serial, BinSession $session, Request $request, DetectionPipelineService $pipeline): JsonResponse
    {
        $bin = Bin::where('serial_number', $serial)->where('status', 'active')->firstOrFail();

        if ($session->bin_id !== $bin->id) {
            return response()->json(['error' => 'Session does not belong to this bin'], 403);
        }

        if (! $session->isActive()) {
            return response()->json(['error' => 'Session is not active'], 400);
        }

        $request->validate([
            'image' => 'required|string',
            'input_method' => 'required|in:cup_slot,lid_slot,straw_slot,general_intake',
        ]);

        $event = $pipeline->processFrame($session, $request->image, $request->input_method);

        if (! $event) {
            return response()->json(['message' => 'No item detected'], 200);
        }

        $bin->refresh();

        return response()->json([
            'detection' => [
                'id' => $event->id,
                'waste_type' => $event->waste_type,
                'input_method' => $event->input_method,
                'confidence' => $event->confidence,
                'detected_brand' => $event->detectedBrand?->name,
                'image_path' => $event->image_path,
                'created_at' => $event->created_at->toISOString(),
            ],
            'bin' => [
                'fill_level' => $bin->fill_level,
                'weight_grams' => $bin->weight_grams,
                'sensor_levels' => $bin->sensor_levels,
            ],
        ], 201);
    }

    public function endSession(string $serial, BinSession $session, PointsCalculatorService $calculator): JsonResponse
    {
        $bin = Bin::where('serial_number', $serial)->where('status', 'active')->firstOrFail();

        if ($session->bin_id !== $bin->id) {
            return response()->json(['error' => 'Session does not belong to this bin'], 403);
        }

        $session->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        $points = 0;

        if ($session->user_id) {
            $points = $calculator->calculate($session);

            if ($points > 0) {
                $itemCount = $session->detectionEvents()->count();
                $behaviorLabel = $session->cup_rinsed ? 'separated + rinsed' : 'standard';

                RecyclingTransaction::create([
                    'user_id' => $session->user_id,
                    'bin_session_id' => $session->id,
                    'type' => 'earned',
                    'points' => $points,
                    'description' => "{$itemCount} items recycled ({$behaviorLabel}) at {$bin->outlet?->name}",
                ]);

                $user = User::find($session->user_id);
                $user->increment('points_balance', $points);
                $user->update([
                    'last_recycled_at' => now(),
                    'current_streak' => $user->current_streak + 1,
                    'longest_streak' => max($user->longest_streak, $user->current_streak + 1),
                ]);
            }
        }

        return response()->json([
            'session' => [
                'id' => $session->id,
                'status' => 'completed',
                'items_count' => $session->detectionEvents()->count(),
                'cup_rinsed' => $session->cup_rinsed,
                'points_earned' => $points,
                'user_linked' => (bool) $session->user_id,
            ],
        ]);
    }

    public function markRinsed(string $serial, BinSession $session): JsonResponse
    {
        $bin = Bin::where('serial_number', $serial)->where('status', 'active')->firstOrFail();

        if ($session->bin_id !== $bin->id) {
            return response()->json(['error' => 'Session does not belong to this bin'], 403);
        }

        $session->update(['cup_rinsed' => true]);

        return response()->json(['message' => 'Cup rinse recorded', 'cup_rinsed' => true]);
    }
}

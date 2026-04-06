<?php

use App\Http\Controllers\Api\DetectionPipelineController;
use App\Models\Bin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Bin Detection Pipeline (no auth — bin-initiated IoT endpoints)
|--------------------------------------------------------------------------
*/

Route::get('v1/bins/active', [DetectionPipelineController::class, 'listBins']);

Route::get('v1/bins/resolve/{serial}', function (string $serial) {
    $bin = Bin::where('serial_number', $serial)->firstOrFail();

    return response()->json(['data' => ['id' => $bin->id, 'serial_number' => $bin->serial_number]]);
});

Route::post('v1/bins/{bin}/heartbeat', function (Request $request, Bin $bin) {
    $bin->update([
        'fill_level' => $request->input('fill_level', $bin->fill_level),
        'weight_grams' => $request->input('weight_grams', $bin->weight_grams),
        'sensor_levels' => $request->input('sensor_levels', $bin->sensor_levels),
    ]);

    return response()->json(['data' => $bin->fresh(), 'message' => 'Heartbeat received.']);
});

Route::prefix('v1/bin/{serial}')->group(function (): void {
    Route::post('sessions', [DetectionPipelineController::class, 'startSession']);
    Route::post('sessions/{session}/link-user', [DetectionPipelineController::class, 'linkUser']);
    Route::post('sessions/{session}/detect', [DetectionPipelineController::class, 'detect']);
    Route::post('sessions/{session}/end', [DetectionPipelineController::class, 'endSession']);
    Route::post('sessions/{session}/rinse', [DetectionPipelineController::class, 'markRinsed']);
});

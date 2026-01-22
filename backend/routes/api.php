<?php

use App\Http\Controllers\Api\BinController;
use App\Http\Controllers\Api\Example\PersonController;
use App\Http\Controllers\Api\OutletController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->group(function (): void {
    Route::apiResource('persons', PersonController::class);
    Route::apiResource('outlets', OutletController::class);
    Route::apiResource('bins', BinController::class);
    Route::post('bins/{bin}/assign', [BinController::class, 'assign'])->name('bins.assign');
    Route::post('bins/{bin}/unassign', [BinController::class, 'unassign'])->name('bins.unassign');
});

<?php

use App\Http\Controllers\Api\PersonController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->group(function (): void {
    Route::apiResource('persons', PersonController::class);
});

<?php

use App\Http\Controllers\Example\PersonController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('pages.examples.home'))->name('home');
Route::get('/components', fn () => view('pages.examples.components-demo'));

Route::get('/persons', [PersonController::class, 'index'])->name('persons.index');
Route::get('/persons/create', [PersonController::class, 'create'])->name('persons.create');
Route::post('/persons', [PersonController::class, 'store'])->name('persons.store');
Route::get('/persons/{person}', [PersonController::class, 'show'])->name('persons.show');
Route::get('/persons/{person}/edit', [PersonController::class, 'edit'])->name('persons.edit');
Route::put('/persons/{person}', [PersonController::class, 'update'])->name('persons.update');
Route::delete('/persons/{person}', [PersonController::class, 'destroy'])->name('persons.destroy');

Route::get('/wide-events-log', function () {
    if (! app()->isLocal()) {
        abort(403);
    }

    $path = storage_path('logs/wide-events.log');

    if (! File::exists($path)) {
        abort(404);
    }

    return response()->stream(function () use ($path): void {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return;
        }

        while (! feof($handle)) {
            echo fread($handle, 8192);
        }

        fclose($handle);
    }, 200, [
        'Content-Type' => 'text/plain; charset=utf-8',
        'Cache-Control' => 'no-store',
    ]);
});

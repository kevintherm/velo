<?php

use App\Http\Controllers\RecordController;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Support\Facades\Route;

/**
 * API Routes
 * !IMPORTANT: Request MUST send Accept headers of application/json when using laravel validation else laravel will throw a web 404 response.
 */

Route::prefix('collections/{collection:name}/records')->group(function () {

    Route::get('/', [RecordController::class, 'index'])->name('index');
    Route::get('/{recordId}', [RecordController::class, 'show'])->name('show');
    Route::post('/', [RecordController::class, 'store'])->name('store');
    Route::put('/{recordId}', [RecordController::class, 'update'])->name('update');
    Route::delete('/{record}', [RecordController::class, 'delete'])->name('delete');

})->name('records.');
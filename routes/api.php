<?php

use App\Http\Controllers\MyClientController;
use Illuminate\Routing\Route;

Route::prefix('client')->group(function () {
    Route::post('/', [MyClientController::class, 'store']);
    Route::get('/{slug}', [MyClientController::class, 'show']);
    Route::put('/{id}', [MyClientController::class, 'update']);
    Route::delete('/{id}', [MyClientController::class, 'destroy']);
});

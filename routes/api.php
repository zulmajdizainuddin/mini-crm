<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\CompanyController;
use Illuminate\Support\Facades\Route;

/*
| API v1 — token authenticated with Laravel Sanctum.
| 1. POST /api/v1/auth/token with the admin credentials to get a Bearer token.
| 2. Send `Authorization: Bearer <token>` with the requests below.
*/
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('auth/token', [AuthTokenController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('auth.token.store');

    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('auth/token', [AuthTokenController::class, 'destroy'])->name('auth.token.destroy');

        Route::apiResource('companies', CompanyController::class)->only(['index', 'show']);
    });
});

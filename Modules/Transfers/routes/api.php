<?php

use Illuminate\Support\Facades\Route;
use Modules\Transfers\Http\Controllers\TransfersController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('transfers', TransfersController::class)->names('transfers');
});

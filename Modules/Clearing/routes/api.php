<?php

use Illuminate\Support\Facades\Route;
use Modules\Clearing\Http\Controllers\ClearingController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('clearings', ClearingController::class)->names('clearing');
});

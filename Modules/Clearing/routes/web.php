<?php

use Illuminate\Support\Facades\Route;
use Modules\Clearing\Http\Controllers\ClearingController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('clearings', ClearingController::class)->names('clearing');
});

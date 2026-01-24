<?php

use Illuminate\Support\Facades\Route;
use Modules\Transfers\Http\Controllers\TransfersController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('transfers', TransfersController::class)->names('transfers');
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\OAuthCallbackController; // Dodano import

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/onboarding', function () { // Nowa trasa dla onboardingu
    return view('onboarding');
})->name('onboarding');

// Dodano trasę dla callbacku OAuth Hubsys
Route::get('/oauth/callback/hubsys', [OAuthCallbackController::class, 'handleHubsysCallback'])
    ->name('oauth.callback.hubsys');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Przekieruj domyślną trasę rejestracji na nowy proces onboardingu
Route::redirect('/register', '/onboarding')->name('register'); // Dodano

require __DIR__.'/settings.php';

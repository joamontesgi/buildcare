<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::redirect('/', '/login');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('up', fn () => response('', 200));

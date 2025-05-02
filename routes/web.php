<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tasks\Tasks;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::view('profile', 'profile')->name('profile');

    Route::get('tasks', Tasks::class)->name('tasks');
});

require __DIR__.'/auth.php';

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserControllers;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
});


Volt::route('/cashier', 'cashier-dashboard');
Volt::route('/queue/status/{token}', 'show')->name('queue.status');


Route::post('/submit', [UserControllers::class, 'store'])->name('submit.form');
Route::get('/queue/check-device', [UserControllers::class, 'checkDevice']);
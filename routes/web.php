<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PurPOController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('home');
});

// Purchasing
// Purchase Order
Route::resource('purPO', PurPOController::class);



// Production
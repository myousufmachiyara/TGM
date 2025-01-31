<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\PurPOController;
use App\Http\Controllers\ProductCategoriesController;
use App\Http\Controllers\ProductAttributesController;
use App\Http\Controllers\SubHeadOfAccController;
use App\Http\Controllers\COAController;

Route::get('/', function () {
    return view('home');
});

Route::get('/shopify/products', [ShopifyController::class, 'index']);
Route::get('/shopify/products/{productId}', [ShopifyController::class, 'show']);

// Accounts
Route::resource('shoa', SubHeadOfAccController::class);
Route::resource('coa', COAController::class);

// Products
Route::resource('product-attributes', ProductAttributesController::class);
Route::resource('product-categories', ProductCategoriesController::class);
Route::resource('products', ProductsController::class);

// Purchase Orders
Route::resource('pur-pos', PurPOController::class);
Route::resource('pur-fgpos', PurFGPOController::class);
Route::get('pur-pos/print/{id}', [PurPoController::class, 'print'])->name('pur-pos.print'); // Exceptional Route


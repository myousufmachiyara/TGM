<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\PurPOController;
use App\Http\Controllers\ProductCategoriesController;
use App\Http\Controllers\ProductMeasurementUnitsController;
use App\Http\Controllers\ProductColorsController;

Route::get('/', function () {
    return view('home');
});

Route::get('/shopify/products', [ShopifyController::class, 'index']);
Route::get('/shopify/products/{productId}', [ShopifyController::class, 'show']);

Route::resource('purpos', PurPOController::class);
Route::resource('product-categories', ProductCategoriesController::class);
Route::resource('product-measurement-units', ProductMeasurementUnitsController::class);
Route::resource('product-colors', ProductColorsController::class);
Route::resource('products', ProductsController::class);

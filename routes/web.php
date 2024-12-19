<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\PurPOController;
use App\Http\Controllers\ProductEntitiesController;


Route::get('/', function () {
    return view('home');
});
Route::get('/home', function () {
    return view('home');
});

Route::get('/shopify/products', [ShopifyController::class, 'index']);
Route::get('/shopify/products/{productId}', [ShopifyController::class, 'show']);

Route::resource('purpos', PurPOController::class);
Route::resource('products', ProductsController::class);

// Dynamic Entity Resource Route (with constraints)
Route::resource('{entity}', ProductEntitiesController::class)
    ->parameters(['{entity}' => 'resource'])
    ->except(['show'])
    ->names([
        'index' => 'entity.index',
        'create' => 'entity.create',
        'store' => 'entity.store',
        'edit' => 'entity.edit',
        'update' => 'entity.update',
        'destroy' => 'entity.destroy',
    ]);
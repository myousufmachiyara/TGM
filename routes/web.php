<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\PurPOController;
use App\Http\Controllers\ProductCategoriesController;
use App\Http\Controllers\ProductAttributesController;
use App\Http\Controllers\SubHeadOfAccController;
use App\Http\Controllers\COAController;
use App\Http\Controllers\PurFGPOController;

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
Route::get('pur-fgpos-rec', [PurFGPOController::class, 'receiving'])->name('pur-fgpos.rec');
Route::get('pur-fgpos-new-challan', [PurFGPOController::class, 'newChallan'])->name('pur-fgpos.new-challan');
Route::get('pur-pos/print/{id}', [PurPoController::class, 'print'])->name('pur-pos.print'); // Exceptional Route
Route::get('pur-fgpos/print/{id}', [PurFGPOController::class, 'print'])->name('pur-fgpos.print'); // Exceptional Route

// PO Receiving


// Vouchers

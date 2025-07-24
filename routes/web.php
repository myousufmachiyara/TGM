<?php

use App\Http\Controllers\COAController;
use App\Http\Controllers\JournalVoucher1Controller;
use App\Http\Controllers\POBillsController;
use App\Http\Controllers\ProductAttributesController;
use App\Http\Controllers\ProductCategoriesController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\PurFGPOController;
use App\Http\Controllers\PurPOController;
use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\SubHeadOfAccController;
use App\Http\Controllers\PaymentVoucherController;

use Illuminate\Support\Facades\Route;

Auth::routes([
    'register' => false, // Disable registration
]);

Route::middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('/shopify/products', [ShopifyController::class, 'index']);
    Route::get('/shopify/products/{productId}', [ShopifyController::class, 'show']);

    // Accounts
    Route::resource('shoa', SubHeadOfAccController::class);
    Route::resource('coa', COAController::class);

    // Products
    Route::resource('product-attributes', ProductAttributesController::class);
    Route::resource('product-categories', ProductCategoriesController::class);
    Route::resource('products', ProductsController::class);
    Route::post('productDetails', [ProductsController::class, 'getProductDetails'])->name('product.details');
    Route::get('/attributes/{id}/values', [ProductAttributesController::class, 'getAttributeValues']);

    // Purchase Orders
    Route::resource('pur-pos', PurPOController::class);
    Route::get('pur-pos-rec/{id}', [PurPOController::class, 'receiving'])->name('pur-pos.rec');
    Route::post('pur-pos-received', [PurPOController::class, 'storeReceiving'])->name('pur-pos.store-rec');
    Route::post('/get-po-codes', [PurPOController::class, 'getPoCodes'])->name('get.po.codes');
    Route::get('/get-po-width', [PurPOController::class, 'getWidth'])->name('get.po.width');
    
    Route::resource('pur-fgpos', PurFGPOController::class);
    Route::get('pur-fgpos-rec/{id}', [PurFGPOController::class, 'receiving'])->name('pur-fgpos.rec');
    Route::post('pur-fgpos-received', [PurFGPOController::class, 'storeReceiving'])->name('pur-fgpos.store-rec');
    Route::get('pur-fgpos-new-challan', [PurFGPOController::class, 'newChallan'])->name('pur-fgpos.new-challan');
    Route::get('pur-fgpos-get-details', [PurFGPOController::class, 'getDetails'])->name('pur-fgpos.get-details');

    Route::get('pur-pos/print/{id}', [PurPOController::class, 'print'])->name('pur-pos.print'); // Exceptional Route
    Route::get('pur-fgpos/print/{id}', [PurFGPOController::class, 'print'])->name('pur-fgpos.print'); // Exceptional Route

    // Billing
    Route::resource('fgpo-bills', POBillsController::class);

    // Payment Vouchers
    Route::resource('payment-vouchers', PaymentVoucherController::class);

});

Auth::routes();

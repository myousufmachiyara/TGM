<?php

namespace App\Http\Controllers;

use App\Services\ShopifyService;

class ShopifyController extends Controller
{
    protected $shopifyService;

    public function __construct(ShopifyService $shopifyService)
    {
        $this->shopifyService = $shopifyService;
    }

    public function index()
    {
        $products = $this->shopifyService->getProducts();

        return view('shopify.index', compact('products'));
    }

    public function show($productId)
    {
        $product = $this->shopifyService->getProductById($productId);

        return view('shopify.show', compact('product'));
    }
}

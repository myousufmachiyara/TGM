<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;

class ShopifyService
{
    protected $client;
    protected $storeUrl;
    protected $accessToken;

    public function __construct()
    {
        $this->client = new Client();
        $this->storeUrl = Config::get('shopify.store_url');
        $this->accessToken = Config::get('shopify.access_token');
    }

    /**
     * Get the Shopify API URL.
     *
     * @param string $endpoint
     * @return string
     */
    private function getApiUrl($endpoint)
    {
        return "https://{$this->storeUrl}/admin/api/2023-10/{$endpoint}.json";
    }

    /**
     * Get Shopify products.
     *
     * @return array
     */
    public function getProducts()
    {
        $response = $this->client->get($this->getApiUrl('products'), [
            'headers' => [
                'X-Shopify-Access-Token' => $this->accessToken,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get a single product by ID.
     *
     * @param int $productId
     * @return array
     */
    public function getProductById($productId)
    {
        $response = $this->client->get($this->getApiUrl("products/{$productId}"), [
            'headers' => [
                'X-Shopify-Access-Token' => $this->accessToken,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    // Add other API calls as needed (e.g., orders, customers)
}

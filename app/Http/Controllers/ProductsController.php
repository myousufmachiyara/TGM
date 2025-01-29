<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\ProductCategory;
use App\Models\ProductAttributes;
use App\Models\ProductVariation;
use App\Models\ProductAttachements;

use Validator;
use Exception;

class ProductsController extends Controller
{
    public function index()
    {
        $products = Products::with('category')->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $prodCat = ProductCategory::all();  // Get all product categories
        $attributes = ProductAttributes::with('values')->get();

        return view('products.create', compact('prodCat','attributes'));
    }

    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'required|string|max:255|unique:products,sku', // Ensure SKU is unique
                'description' => 'nullable|string',
                'category_id' => 'required|exists:product_categories,id', // Make sure category exists
                'measurement_unit' => 'nullable|string|max:50',
                'price' => 'nullable|numeric|min:0',
                'sale_price' => 'nullable|numeric|min:0',
                'purchase_note' => 'nullable|string',
                'images' => 'nullable|array',
            ]);

            // Create the product
            $product = Products::create([
                'name' => $request->name,
                'sku' => $request->sku,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'measurement_unit' => $request->measurement_unit,
                'price' => $request->price,
                'sale_price' => $request->sale_price,
                'purchase_note' => $request->purchase_note,
            ]);

            // Handle variations (if provided)
            if ($request->has('variations') && is_array($request->variations)) {
                foreach ($request->variations as $variation) {
                    ProductVariation::create([
                        'product_id' => $product->id,
                        'sku' => $variation['sku'],
                        'price' => $variation['price'],
                        'stock' => $variation['stock'],
                        'attribute_id' => $variation['attribute_id'],
                        'value_id' => $variation['value_id'],
                    ]);
                }
            }

            // Handle images (if provided)
            if ($request->has('images') && is_array($request->images)) {
                foreach ($request->images as $image) {
                    ProductAttachements::create([
                        'product_id' => $product->id,
                        'image_path' => $image['image_path'],
                        'is_primary' => $image['is_primary'] ?? false, // If no is_primary is provided, default to false
                    ]);
                }
            }

            return redirect()->route('products.index')->with('success', 'Product created successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
   
    public function show($id)
    {
        // Find the product by ID
        $product = Product::findOrFail($id);
        return view('products.show', compact('product'));
    }

    public function edit($id)
    {
        // Find the product by ID
        $product = Product::findOrFail($id);
        return view('products.edit', compact('product'));
    }


    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        // Find and update the product
        $product = Product::findOrFail($id);
        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
        ]);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

   
    public function destroy($id)
    {
        // Find and delete the product
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}

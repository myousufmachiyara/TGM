<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\ProductCategory;
use App\Models\ProductAttributes;
use App\Models\ProductVariations;
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

    // public function store(Request $request)
    // {
    //     try {
    //         $validatedData = $request->validate([
    //             'name' => 'required|string|max:255',
    //             'sku' => 'required|string|max:255|unique:products,sku',
    //             'category_id' => 'required|exists:product_categories,id',
    //             'price' => 'required|numeric|min:0',
    //             'sale_price' => 'required|numeric|min:0',
    //             'opening_stock' => 'required|numeric|min:0',
    //             'measurement_unit' => 'nullable|string|max:50',
    //             'description' => 'nullable|string',
    //             'purchase_note' => 'nullable|string',
    //             'prod_att' => 'nullable|array',
    //             'prod_att.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //             'item_type' => 'nullable|string|max:50',
    //          ]);
    
    //         // Create the product
    //         $product = Products::create($validatedData);
    
    //         // Handle variations
    //         if ($request->has('has_variations') && $request->has_variations == 1) {
    //             $validatedVariations = $request->validate([
    //                 'variations' => 'required|array|min:1',
    //                 'variations.*.sku' => 'required|string|max:255|unique:product_variations,sku',
    //                 'variations.*.price' => 'required|numeric|min:0',
    //                 'variations.*.stock' => 'required|numeric|min:0',
    //                 'variations.*.attribute_id' => 'required|exists:product_attributes,id',
    //                 'variations.*.attribute_value_id' => 'required|exists:product_attributes_values,id',
    //             ]);
    
    //             foreach ($validatedVariations['variations'] as $variation) {
    //                 foreach ($variation['attribute_value_id'] as $valueId) { // Handle multiple values
    //                     ProductVariation::create([
    //                         'product_id' => $product->id,
    //                         'sku' => $variation['sku'],
    //                         'price' => $variation['price'],
    //                         'stock' => $variation['stock'],
    //                         'attribute_id' => $variation['attribute_id'],
    //                         'attribute_value_id' => $valueId, // Save each value separately
    //                     ]);
    //                 }
    //             }
    //         }
    
    //         return redirect()->route('products.index')->with('success', 'Product created successfully.');
    //     } catch (\Exception $e) {
    //         return back()->withErrors(['error' => $e->getMessage()])->withInput();
    //     }
    // }
    
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'required|string|max:255|unique:products,sku',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:product_categories,id',
                'measurement_unit' => 'nullable|string|max:50',
                'item_type' => 'nullable|string|max:50',
                'price' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0',
                'purchase_note' => 'nullable|string',
                'opening_stock' => 'required|numeric|min:0',
                'prod_att' => 'nullable|array',
                'prod_att.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'variations' => 'nullable|array',
                'variations.*.sku' => 'required|string|max:255|unique:product_variations,sku',
                'variations.*.price' => 'required|numeric|min:0',
                'variations.*.stock' => 'required|numeric|min:0',
                'variations.*.attribute_id' => 'required|exists:product_attributes,id',
                'variations.*.attribute_value_id' => 'required|exists:product_attributes_values,id',
            ]);
    
            // Create the product
            $product = Products::create($validatedData);
    
            // Handle variations
            if ($request->has('variations')) {
                foreach ($request->variations as $variation) {
                    ProductVariations::create([
                        'product_id' => $product->id,
                        'sku' => $variation['sku'],
                        'price' => $variation['price'],
                        'stock' => $variation['stock'],
                        'attribute_id' => $variation['attribute_id'],
                        'attribute_value_id' => $variation['attribute_value_id'],
                    ]);
                }
            }
    
            // Handle images
            if ($request->hasFile('prod_att')) {
                foreach ($request->file('prod_att') as $image) {
                    $imagePath = $image->store('products/images', 'public');
                    ProductAttachements::create([
                        'product_id' => $product->id,
                        'image_path' => $imagePath,
                        'is_primary' => false,
                    ]);
                }
            }
    
            return redirect()->route('products.index')->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
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

    public function getProductDetails(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'product_ids' => 'required|array',
                'product_ids.*' => 'exists:products,id',
            ]);
    
            // Fetch all selected products with their variations
            $products = Products::with('variations')->whereIn('id', $request->product_ids)->get();
    
            return response()->json($products, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

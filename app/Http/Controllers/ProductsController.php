<?php

namespace App\Http\Controllers;

use App\Models\ProductAttachements;
use App\Models\ProductAttributes;
use App\Models\ProductCategory;
use App\Models\Products;
use App\Models\ProductVariations;
use App\Models\ProductAttributesValues;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

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

        return view('products.create', compact('prodCat', 'attributes'));
    }

    public function store(Request $request)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:product_categories,id',
                'measurement_unit' => 'nullable|string|max:50',
                'width' => '',
                'item_type' => 'nullable|string|max:50',
                'price' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0',
                'purchase_note' => 'nullable|string',
                'opening_stock' => 'required|numeric|min:0',
                'prod_att' => 'nullable|array',
                'prod_att.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'variations' => 'nullable|array',
                'variations.*.price' => 'required|numeric|min:0',
                'variations.*.stock' => 'required|numeric|min:0',
                'variations.*.attribute_id' => 'required|exists:product_attributes,id',
                'variations.*.attribute_value_id' => 'required|exists:product_attributes_values,id',
            ]);
    
            // Wrap entire logic in DB transaction
            DB::beginTransaction();
    
            // Get category code
            $category = ProductCategory::findOrFail($validatedData['category_id']);
            $categoryCode = $category->cat_code;
    
            // Get latest product in this category
            $lastProduct = Products::where('category_id', $validatedData['category_id'])
                ->orderByDesc('id')
                ->first();
    
            if ($lastProduct && preg_match('/(\d+)$/', $lastProduct->sku, $matches)) {
                $lastNumber = (int) $matches[1];
            } else {
                $lastNumber = 0;
            }
    
            $nextSequence = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
            $sku = $categoryCode . '-' . $nextSequence;
    
            // Assign SKU
            $validatedData['sku'] = $sku;
    
            // Create the product
            $product = Products::create($validatedData);
    
            // Handle variations
            if ($request->has('variations')) {
                foreach ($request->variations as $variation) {
                    $attrValue = ProductAttributesValues::findOrFail($variation['attribute_value_id']);
                    $variationSlug = Str::slug($attrValue->value);
                    $variationSku = $sku . '-' . strtoupper($variationSlug);
    
                    ProductVariations::create([
                        'product_id' => $product->id,
                        'price' => $variation['price'],
                        'stock' => $variation['stock'],
                        'attribute_id' => $variation['attribute_id'],
                        'attribute_value_id' => $variation['attribute_value_id'],
                        'sku' => $variationSku,
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
                    ]);
                }
            }
    
            DB::commit(); // Commit all if everything is successful
    
            return redirect()->route('products.index')->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback everything on any failure
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
        $product = Products::with(['variations', 'attachments'])->findOrFail($id);
        $prodCat = ProductCategory::all(); // if needed for select dropdowns
        $attributes = ProductAttributes::with('values')->get(); // if you're using attributes in variations
    
        // Debugging with dd()
        dd($product, $prodCat, $attributes);
    
        // Alternatively, you can use print_r for each variable:
        // print_r($product);
        // print_r($prodCat);
        // print_r($attributes);
        return view('products.edit', compact('product', 'prodCat', 'attributes'));
    }

    public function update(Request $request, $id)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:product_categories,id',
                'measurement_unit' => 'nullable|string|max:50',
                'width' => '',
                'item_type' => 'nullable|string|max:50',
                'price' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0',
                'purchase_note' => 'nullable|string',
                'opening_stock' => 'required|numeric|min:0',
                'prod_att' => 'nullable|array',
                'prod_att.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'variations' => 'nullable|array',
                'variations.*.price' => 'required|numeric|min:0',
                'variations.*.stock' => 'required|numeric|min:0',
                'variations.*.attribute_id' => 'required|exists:product_attributes,id',
                'variations.*.attribute_value_id' => 'required|exists:product_attributes_values,id',
            ]);
    
            DB::beginTransaction();
    
            $product = Products::findOrFail($id);
    
            // SKU should not change during update
            $validatedData['sku'] = $product->sku;
    
            // Update main product
            $product->update($validatedData);
    
            // === Handle Variations ===
            if ($request->has('variations')) {
                // Remove old variations
                ProductVariations::where('product_id', $product->id)->delete();
    
                foreach ($request->variations as $variation) {
                    $attrValue = ProductAttributesValues::findOrFail($variation['attribute_value_id']);
                    $variationSlug = Str::slug($attrValue->value);
                    $variationSku = $product->sku . '-' . strtoupper($variationSlug);
    
                    ProductVariations::create([
                        'product_id' => $product->id,
                        'price' => $variation['price'],
                        'stock' => $variation['stock'],
                        'attribute_id' => $variation['attribute_id'],
                        'attribute_value_id' => $variation['attribute_value_id'],
                        'sku' => $variationSku,
                    ]);
                }
            }
    
            // === Handle New Images ===
            if ($request->hasFile('prod_att')) {
                foreach ($request->file('prod_att') as $image) {
                    $imagePath = $image->store('products/images', 'public');
                    ProductAttachements::create([
                        'product_id' => $product->id,
                        'image_path' => $imagePath,
                    ]);
                }
            }
    
            DB::commit();
    
            return redirect()->route('products.index')->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }    

    public function destroy($id)
    {
        DB::beginTransaction(); // Start the transaction
    
        try {
            // Find the product by ID
            $product = Products::findOrFail($id);
    
            // Optionally, delete related records (e.g., variations, images, attachments) first
            // Deleting variations
            $product->variations()->delete();
    
            // Deleting associated images
            $product->attachments()->delete();
    
            // Delete the product itself (soft delete in this case)
            $product->delete();
    
            DB::commit(); // Commit the transaction if all operations succeed
    
            // Redirect back with a success message
            return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
        } catch (Exception $e) {
            DB::rollBack(); // Roll back the transaction if something goes wrong
    
            // Log the error if needed
            \Log::error('Error deleting product: ' . $e->getMessage());
    
            // Redirect back with an error message
            return redirect()->route('products.index')->with('error', 'Failed to delete product. Please try again.');
        }
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

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
        $products = Products::with(['category', 'firstAttachment'])->get();
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
                'name' => 'required|string|max:255|unique:products',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:product_categories,id',
                'measurement_unit' => 'required|string|max:50',
                'item_type' => 'required|string|max:50',
                'style' => 'nullable|string|max:50',
                'material' => 'nullable|string|max:50',
                'price' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0',
                'purchase_note' => 'nullable|string',
                'opening_stock' => 'nullable|numeric|min:0',
                'prod_att' => 'required|array',
                'prod_att.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp',
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
    
        return view('products.edit', compact('product', 'prodCat', 'attributes'));
    }

    public function getValues($id)
    {
        $attribute = Attribute::with('values')->find($id);

        if (!$attribute) {
            return response()->json(['error' => 'Attribute not found'], 404);
        }

        return response()->json($attribute->values);
    }

    public function update(Request $request, $id)
    {
        try {
            // Validate incoming data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:products,name,' . $id,
                'description' => 'nullable|string',
                'category_id' => 'required|exists:product_categories,id',
                'measurement_unit' => 'nullable|string|max:50',
                'item_type' => 'nullable|string|max:50',
                'style' => 'nullable|string|max:50',
                'material' => 'nullable|string|max:50',
                'purchase_note' => 'nullable|string',
                'opening_stock' => 'required|numeric|min:0',
                'prod_att' => 'nullable|array',
                'prod_att.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp',
                'variations' => 'nullable|array',
                'variations.*.price' => 'required|numeric|min:0',
                'variations.*.stock' => 'required|numeric|min:0',
                'variations.*.attribute_id' => 'required|exists:product_attributes,id',
                'variations.*.attribute_value_id' => 'required|exists:product_attributes_values,id',
                'variations.*.sku' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // Fetch product
            $product = Products::findOrFail($id);

            // Update basic fields
            $product->update([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'] ?? null,
                'category_id' => $validatedData['category_id'],
                'measurement_unit' => $validatedData['measurement_unit'] ?? null,
                'item_type' => $validatedData['item_type'] ?? null,
                'style' => $validatedData['style'] ?? null,
                'material' => $validatedData['material'] ?? null,
                'purchase_note' => $validatedData['purchase_note'] ?? null,
                'opening_stock' => $validatedData['opening_stock'],
            ]);

            // Handle variations
            if ($request->has('variations')) {
                // Delete removed variations
                $submittedIds = collect($request->input('variations'))->pluck('attribute_value_id')->toArray();
                ProductVariations::where('product_id', $product->id)
                    ->whereNotIn('attribute_value_id', $submittedIds)
                    ->delete();

                foreach ($request->input('variations') as $variation) {
                    $variationSku = $variation['sku'];

                    // Create or update variation
                    ProductVariations::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'attribute_value_id' => $variation['attribute_value_id']
                        ],
                        [
                            'price' => $variation['price'],
                            'stock' => $variation['stock'],
                            'attribute_id' => $variation['attribute_id'],
                            'sku' => $variationSku ?? $product->sku . '-' . strtoupper(Str::slug(ProductAttributesValues::find($variation['attribute_value_id'])->value)),
                        ]
                    );
                }
            }

            // Handle image uploads
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

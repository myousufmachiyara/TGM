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
use Illuminate\Support\Facades\Validator;

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
            // First level validation
            $validator = Validator::make($request->all(), [
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
                'prod_att' => 'nullable|array',
                'prod_att.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp',
                'has_variations' => 'nullable|in:0,1',
                'variations' => 'nullable|array',
            ]);

            // Conditional validation for variations only if has_variations is 1
            $validator->sometimes('variations.*.price', 'required|numeric|min:0', function ($input) {
                return $input->has_variations == 1;
            });

            $validator->sometimes('variations.*.stock', 'required|numeric|min:0', function ($input) {
                return $input->has_variations == 1;
            });

            $validator->sometimes('variations.*.attribute_id', 'required|exists:product_attributes,id', function ($input) {
                return $input->has_variations == 1;
            });

            $validator->sometimes('variations.*.attribute_value_id', 'required|exists:product_attributes_values,id', function ($input) {
                return $input->has_variations == 1;
            });

            // If validation fails, redirect back
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $validatedData = $validator->validated();

            // Begin transaction
            DB::beginTransaction();

            $category = ProductCategory::findOrFail($validatedData['category_id']);
            $categoryCode = $category->cat_code;

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

            $validatedData['sku'] = $sku;

            // Create product
            $product = Products::create($validatedData);

            // Variations (only if has_variations == 1)
            if ($request->has('has_variations') && $request->has_variations == 1) {
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

            // Attach images
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

            return redirect()->route('products.index')->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
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

    public function update(Request $request, $id)
    {
        try {
            $product = Products::with('variations')->findOrFail($id);

            // Validate input
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:products,name,' . $id,
                'description' => 'nullable|string',
                'category_id' => 'required|exists:product_categories,id',
                'measurement_unit' => 'required|string|max:50',
                'item_type' => 'required|string|max:50',
                'style' => 'nullable|string|max:50',
                'material' => 'nullable|string|max:50',
                'purchase_note' => 'nullable|string',
                'opening_stock' => 'nullable|numeric|min:0',
                'prod_att' => 'nullable|array',
                'prod_att.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp',
                'variations' => 'nullable|array',
                'variations.*.price' => 'required|numeric|min:0',
                'variations.*.stock' => 'required|numeric|min:0',
                'variations.*.attribute_id' => 'required|exists:product_attributes,id',
                'variations.*.attribute_value_id' => 'required|exists:product_attributes_values,id',
            ]);

            DB::beginTransaction();

            // Update product fields (excluding SKU)
            $product->update([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'] ?? null,
                'category_id' => $validatedData['category_id'],
                'measurement_unit' => $validatedData['measurement_unit'],
                'item_type' => $validatedData['item_type'],
                'style' => $validatedData['style'] ?? null,
                'material' => $validatedData['material'] ?? null,
                'purchase_note' => $validatedData['purchase_note'] ?? null,
                'opening_stock' => $validatedData['opening_stock'] ?? 0,
            ]);

            $existingVariationIds = $product->variations->pluck('id')->toArray();
            $submittedVariationKeys = array_keys($request->variations ?? []);

            $processedIds = [];

            foreach ($request->variations ?? [] as $key => $variation) {
                if (is_numeric($key)) {
                    // Existing variation - update
                    $existing = $product->variations->get($key);
                    if ($existing) {
                        $existing->update([
                            'price' => $variation['price'],
                            'stock' => $variation['stock'],
                        ]);
                        $processedIds[] = $existing->id;
                    }
                } elseif (Str::startsWith($key, 'new_')) {
                    // New variation - create
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

            // Delete removed variations
            $toDelete = array_diff($existingVariationIds, $processedIds);
            ProductVariations::whereIn('id', $toDelete)->delete();

            if ($request->filled('remove_image_ids')) {
                $idsToRemove = explode(',', $request->remove_image_ids);

                $attachments = ProductAttachements::whereIn('id', $idsToRemove)->get();

                foreach ($attachments as $attachment) {
                    Storage::disk('public')->delete($attachment->image_path);
                    $attachment->delete();
                }
            }
            
            // Handle new attachments
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

    public function getValues($id)
    {
        $attribute = Attribute::with('values')->find($id);

        if (!$attribute) {
            return response()->json(['error' => 'Attribute not found'], 404);
        }

        return response()->json($attribute->values);
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

    public function getVariations($id)
    {
        $item = Products::with('variations')->findOrFail($id);

        return response()->json([
            'variations' => $item->variations
        ]);
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoriesController extends Controller
{
    public function index()
    {
        $items = ProductCategory::all();

        return view('products.categories', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        ProductCategory::create($validated);

        return redirect()->route('product-categories.index')->with('success', 'Category created successfully.');
    }

    public function update(Request $request, string $id) {}

    public function destroy(string $id)
    {
        $item = ProductCategory::findOrFail($id);
        $item->delete(); // Delete category

        return redirect()->route('product-categories.index')->with('success', 'Category deleted successfully.');
    }
}

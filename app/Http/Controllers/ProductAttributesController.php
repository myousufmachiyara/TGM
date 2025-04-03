<?php

namespace App\Http\Controllers;

use App\Models\ProductAttributes;
use Illuminate\Http\Request;

class ProductAttributesController extends Controller
{
    public function index()
    {
        $attributes = ProductAttributes::with('values')->get();

        return view('products.attributes', compact('attributes'));
    }

    public function show($id)
    {
        $attribute = ProductAttributes::with('values')->find($id);

        if (! $attribute) {
            return response()->json(['message' => 'Product Attribute not found'], 404);
        }

        return response()->json($attribute);
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'values' => 'required|string', // Ensure it's a valid string
        ]);

        // Create the product attribute
        $attribute = ProductAttributes::create([
            'name' => $request->name,
        ]);

        // Split the comma-separated values, trim whitespace, and remove empty values
        $values = array_filter(array_map('trim', explode(',', $request->values)));

        // Create individual attribute values
        foreach ($values as $value) {
            $attribute->values()->create(['value' => $value]);
        }

        // Redirect or respond with success
        return redirect()->back()->with('success', 'Product attribute and values created successfully!');
    }

    public function update(Request $request, $id)
    {
        $attribute = ProductAttributes::find($id);

        if (! $attribute) {
            return response()->json(['message' => 'Product Attribute not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'values' => 'array', // Optional: Values for this attribute
            'values.*' => 'string|max:255', // Optional: Ensure all values are strings
        ]);

        if ($request->has('name')) {
            $attribute->update(['name' => $request->name]);
        }

        if ($request->has('values')) {
            // Delete old values and add new ones
            $attribute->values()->delete();

            foreach ($request->values as $value) {
                $attribute->values()->create(['value' => $value]);
            }
        }

        return response()->json(['message' => 'Product Attribute updated successfully', 'data' => $attribute->load('values')]);
    }

    public function destroy($id)
    {
        $attribute = ProductAttributes::find($id);

        if (! $attribute) {
            return response()->json(['message' => 'Product Attribute not found'], 404);
        }

        $attribute->values()->delete(); // Delete associated values
        $attribute->delete();

        return response()->json(['message' => 'Product Attribute deleted successfully']);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductColor;
use Illuminate\Database\QueryException;

class ProductColorsController extends Controller
{

    public function index()
    {
        $items = ProductColor::all(); 
        return view('product-attributes.colors', compact('items')); // Return the view
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:product_colors|max:255',
            ]);
            ProductColor::create($request->only('name'));
            return redirect()->route('product-colors.index')
                ->with('success', 'Color created successfully.');
        } catch (QueryException $e) {
            return back()->withErrors(['error' => 'A database error occurred while creating the Color. Please try again.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|unique:product_colors,name,' . $id . '|max:255',
        ]);

        $item = ProductColor::findOrFail($id); // Find the unit by ID
        $item->update($request->all()); // Update the measurement unit

        return redirect()->route('product-colors.index')
            ->with('success', 'Color updated successfully.'); // Redirect with success message
    }

    public function destroy($id)
    {
        $item = ProductColor::findOrFail($id); // Find the unit by ID
        $item->delete(); // Delete the measurement unit

        return redirect()->route('product-colors.index')->with('success', 'Color deleted successfully.'); // Redirect with success message
    }
}
